<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionArchive;
use App\Models\ElectionSetting;
use App\Models\User;
use App\Models\Vote;
use App\Support\Audit\AuditLogger;
use App\Support\Reports\ExcelReportExporter;
use App\Support\Reports\PdfReportExporter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index', [
            'elections' => $this->scopedElectionQuery()->with('place')->orderBy('name')->get(),
            'reportCards' => $this->reportCards(),
        ]);
    }

    public function export(Request $request, string $type, string $format, ExcelReportExporter $excelExporter, PdfReportExporter $pdfExporter): Response
    {
        abort_unless(array_key_exists($type, $this->reportCards()), 404);
        abort_unless(in_array($format, ['pdf', 'excel'], true), 404);

        $allowedElectionIds = $this->scopedElectionQuery()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $validated = $request->validate([
            'election' => [
                'required',
                Rule::in([
                    'all',
                    ...$allowedElectionIds,
                ]),
            ],
        ], [
            'election.required' => 'Please select an election first.',
            'election.in' => 'Please choose a valid election filter.',
        ]);

        $selectedElection = $validated['election'] === 'all'
            ? null
            : $this->scopedElectionQuery()->findOrFail((int) $validated['election']);

        $payload = match ($type) {
            'election-results' => $this->buildElectionResultsReport($selectedElection),
            'voter-list' => $this->buildVoterListReport($selectedElection),
            'candidate-report' => $this->buildCandidateReport($selectedElection),
            'audit-log' => $this->buildAuditLogReport($selectedElection),
            'election-summary' => $this->buildElectionSummaryReport($selectedElection),
        };

        AuditLogger::record(
            'report_exported',
            auth()->user(),
            "{$payload['title']} exported in ".strtoupper($format)." format.",
            $selectedElection?->id,
            [
                'report_type' => $type,
                'format' => $format,
                'election' => $selectedElection?->name ?? 'All Elections',
            ]
        );

        $filename = str($payload['title'])->slug()->append('-', now()->format('Ymd-His'))->toString();

        return $format === 'pdf'
            ? $pdfExporter->download($payload, $filename)
            : $excelExporter->download($payload, $filename);
    }

    private function buildElectionResultsReport(?Election $selectedElection): array
    {
        $elections = $this->electionScope($selectedElection)
            ->load(['votes', 'users', 'candidates.votes', 'electionSetting']);

        $candidateRows = [];
        $winnerRows = [];
        $electionRows = [];
        $turnoutPercentages = [];

        foreach ($elections as $election) {
            $approvedVoters = $election->users->where('status', 'approved')->count();
            $votesCast = $election->votes->count();
            $presidentVotes = $election->votes->where('position', 'President')->count();
            $vicePresidentVotes = $election->votes->where('position', 'Vice President')->count();
            $turnout = $approvedVoters > 0 ? round(($votesCast / max($approvedVoters * 2, 1)) * 100, 2) : 0;
            $turnoutPercentages[] = $turnout;
            $electionTitle = $this->electionTitleForElection($election);

            $electionRows[] = [
                $this->electionLabel($election),
                $electionTitle,
                $votesCast,
                $presidentVotes,
                $vicePresidentVotes,
                $approvedVoters,
                number_format($turnout, 2).'%',
                $election->electionSetting?->is_active ? 'Active' : ($election->electionSetting?->hasEnded() ? 'Completed' : 'Pending'),
            ];

            foreach ($election->candidates->sortBy('name') as $candidate) {
                $candidateRows[] = [
                    $this->electionLabel($election),
                    $electionTitle,
                    $candidate->name,
                    $candidate->position,
                    $candidate->votes->count(),
                ];
            }

            foreach ($election->candidates->groupBy('position') as $position => $group) {
                $ranked = $group
                    ->sortByDesc(fn (Candidate $candidate) => $candidate->votes->count())
                    ->values();

                $winner = $ranked->get(0);
                $topVotes = $winner?->votes->count() ?? 0;
                $tiedCandidates = $topVotes > 0
                    ? $ranked
                        ->filter(fn (Candidate $candidate) => $candidate->votes->count() === $topVotes)
                        ->sortBy('name')
                        ->values()
                    : collect();
                $isTie = $tiedCandidates->count() > 1;

                $winnerRows[] = [
                    $this->electionLabel($election),
                    $electionTitle,
                    $position ?: 'Other',
                    $isTie ? 'Draw Vote' : 'Winner',
                    $isTie ? implode(', ', $tiedCandidates->pluck('name')->all()) : ($winner?->name ?? 'No winner yet'),
                    $topVotes,
                ];
            }
        }

        return [
            'title' => 'Election Results Report',
            'context' => $this->reportContext($selectedElection),
            'generated_at' => now()->format('M d, Y h:i A'),
            'summary' => [
                ['label' => 'Election Title', 'value' => $this->reportElectionTitleValue($selectedElection)],
                ['label' => 'Tracked elections', 'value' => $elections->count()],
                ['label' => 'Total votes cast', 'value' => $elections->sum(fn (Election $election) => $election->votes->count())],
                ['label' => 'President votes cast', 'value' => $elections->sum(fn (Election $election) => $election->votes->where('position', 'President')->count())],
                ['label' => 'Vice President votes cast', 'value' => $elections->sum(fn (Election $election) => $election->votes->where('position', 'Vice President')->count())],
                ['label' => 'Candidates in report', 'value' => count($candidateRows)],
                ['label' => 'Average turnout', 'value' => number_format(collect($turnoutPercentages)->avg() ?? 0, 2).'%'],
            ],
            'sections' => [
                [
                    'title' => 'Election Wise Vote Counts',
                    'headers' => ['Election', 'Election Title', 'Total Votes', 'President Votes', 'Vice President Votes', 'Approved Voters', 'Turnout', 'Election Status'],
                    'rows' => $electionRows,
                ],
                [
                    'title' => 'Candidate Wise Total Votes',
                    'headers' => ['Election', 'Election Title', 'Candidate', 'Position', 'Votes'],
                    'rows' => $candidateRows,
                ],
                [
                    'title' => 'Winner And Runner Up Summary',
                    'headers' => ['Election', 'Election Title', 'Position', 'Result', 'Winner / Draw Details', 'Top Votes'],
                    'rows' => $winnerRows,
                ],
            ],
            'notes' => [
                'Turnout is calculated against two position votes per approved voter in the selected election scope.',
            ],
        ];
    }

    private function buildVoterListReport(?Election $selectedElection): array
    {
        $users = User::query()
            ->with('election.place', 'election.electionSetting')
            ->where('role', 'user')
            ->when(auth()->user()->isHost(), fn ($query) => $query->whereHas('election', fn ($electionQuery) => $electionQuery->where('host_id', auth()->id())))
            ->when($selectedElection, fn ($query) => $query->where('election_id', $selectedElection->id))
            ->orderBy('name')
            ->get();

        $approvedRows = $users
            ->where('status', 'approved')
            ->map(fn (User $user) => [
                $user->name,
                $this->electionLabel($user->election) ?? $user->last_known_election_name ?? 'Not assigned',
                $this->electionTitleForElection($user->election),
                $user->contact_number,
                $user->hasVoted() ? 'Voted' : 'Not Voted',
            ])
            ->values()
            ->all();

        $reviewRows = $users
            ->whereIn('status', ['pending', 'rejected'])
            ->map(fn (User $user) => [
                $user->name,
                ucfirst($user->status),
                $this->electionLabel($user->election) ?? $user->last_known_election_name ?? 'Not assigned',
                $this->electionTitleForElection($user->election),
                $user->contact_number,
                'Not Voted',
                $user->rejection_message ?: '-',
            ])
            ->values()
            ->all();

        return [
            'title' => 'Voter List Report',
            'context' => $this->reportContext($selectedElection),
            'generated_at' => now()->format('M d, Y h:i A'),
            'summary' => [
                ['label' => 'Election Title', 'value' => $this->reportElectionTitleValue($selectedElection)],
                ['label' => 'Approved voters', 'value' => count($approvedRows)],
                ['label' => 'Pending voters', 'value' => $users->where('status', 'pending')->count()],
                ['label' => 'Rejected voters', 'value' => $users->where('status', 'rejected')->count()],
                ['label' => 'Approved voters who voted', 'value' => $users->where('status', 'approved')->filter(fn (User $user) => $user->hasVoted())->count()],
                ['label' => 'Total voter records', 'value' => $users->count()],
            ],
            'sections' => [
                [
                    'title' => 'Approved Voters',
                    'headers' => ['Name', 'Election', 'Election Title', 'Contact Number', 'Voting Status'],
                    'rows' => $approvedRows,
                ],
                [
                    'title' => 'Pending And Rejected Voters',
                    'headers' => ['Name', 'Status', 'Election', 'Election Title', 'Contact Number', 'Voting Status', 'Message'],
                    'rows' => $reviewRows,
                ],
            ],
            'notes' => [
                'Contact details are included so admins can follow up with pending or rejected voters.',
            ],
        ];
    }

    private function buildCandidateReport(?Election $selectedElection): array
    {
        $candidates = Candidate::query()
            ->with(['election.place', 'election.electionSetting', 'votes'])
            ->when(auth()->user()->isHost(), fn ($query) => $query->whereHas('election', fn ($electionQuery) => $electionQuery->where('host_id', auth()->id())))
            ->when($selectedElection, fn ($query) => $query->where('election_id', $selectedElection->id))
            ->orderBy('name')
            ->get();

        $candidateRows = $candidates
            ->map(fn (Candidate $candidate) => [
                $candidate->name,
                $this->electionLabel($candidate->election) ?? 'Unknown Election',
                $this->electionTitleForElection($candidate->election),
                $candidate->position,
                $candidate->email,
                $candidate->votes->count(),
            ])
            ->values()
            ->all();

        $comparisonRows = $candidates
            ->sortByDesc(fn (Candidate $candidate) => $candidate->votes->count())
            ->values()
            ->map(fn (Candidate $candidate, int $index) => [
                $index + 1,
                $candidate->name,
                $this->electionLabel($candidate->election) ?? 'Unknown Election',
                $this->electionTitleForElection($candidate->election),
                $candidate->votes->count(),
            ])
            ->all();

        return [
            'title' => 'Candidate Report',
            'context' => $this->reportContext($selectedElection),
            'generated_at' => now()->format('M d, Y h:i A'),
            'summary' => [
                ['label' => 'Election Title', 'value' => $this->reportElectionTitleValue($selectedElection)],
                ['label' => 'Candidates listed', 'value' => $candidates->count()],
                ['label' => 'Election coverage', 'value' => $candidates->pluck('election_id')->filter()->unique()->count()],
                ['label' => 'Total votes across candidates', 'value' => $candidates->sum(fn (Candidate $candidate) => $candidate->votes->count())],
            ],
            'sections' => [
                [
                    'title' => 'Candidate Profiles',
                    'headers' => ['Name', 'Election', 'Election Title', 'Position', 'Contact Email', 'Votes'],
                    'rows' => $candidateRows,
                ],
                [
                    'title' => 'Candidate Comparison Table',
                    'description' => 'This table can be opened in Excel for side-by-side vote comparison.',
                    'headers' => ['Rank', 'Candidate', 'Election', 'Election Title', 'Votes'],
                    'rows' => $comparisonRows,
                ],
            ],
            'notes' => [],
        ];
    }

    private function buildAuditLogReport(?Election $selectedElection): array
    {
        $logs = AuditLog::query()
            ->with(['user', 'election.place', 'election.electionSetting'])
            ->when(auth()->user()->isHost(), fn ($query) => $query->whereHas('election', fn ($electionQuery) => $electionQuery->where('host_id', auth()->id())))
            ->when($selectedElection, fn ($query) => $query->where('election_id', $selectedElection->id))
            ->latest('logged_at')
            ->limit(500)
            ->get();

        $rows = $logs
            ->map(fn (AuditLog $log) => [
                optional($log->logged_at)->format('M d, Y h:i:s A') ?? '-',
                $log->user?->name ?? 'System',
                str($log->action)->replace('_', ' ')->title()->toString(),
                $this->electionLabel($log->election ?? $selectedElection) ?? 'General',
                $this->electionTitleForElection($log->election ?? $selectedElection),
                $log->ip_address ?? 'Unknown',
                $log->description,
            ])
            ->values()
            ->all();

        return [
            'title' => 'Audit Log Report',
            'context' => $this->reportContext($selectedElection),
            'generated_at' => now()->format('M d, Y h:i A'),
            'summary' => [
                ['label' => 'Election Title', 'value' => $this->reportElectionTitleValue($selectedElection)],
                ['label' => 'Log entries exported', 'value' => count($rows)],
                ['label' => 'Unique actors', 'value' => $logs->pluck('user_id')->filter()->unique()->count()],
                ['label' => 'Latest activity', 'value' => optional($logs->first()?->logged_at)->format('M d, Y h:i A') ?? 'No activity yet'],
            ],
            'sections' => [
                [
                    'title' => 'Security Review Timeline',
                    'headers' => ['Timestamp', 'Actor', 'Action', 'Election', 'Election Title', 'IP Address', 'Description'],
                    'rows' => $rows,
                ],
            ],
            'notes' => [
                'The audit log records report exports, login success, vote casting, and key admin actions from this update forward.',
            ],
        ];
    }

    private function buildElectionSummaryReport(?Election $selectedElection): array
    {
        $elections = $this->electionScope($selectedElection)
            ->load(['votes', 'users', 'electionSetting']);

        $archives = ElectionArchive::query()
            ->when($selectedElection, fn ($query) => $query->where('election_name', $selectedElection->name))
            ->when(auth()->user()->isHost(), fn ($query) => $query->where('host_id', auth()->id()))
            ->get();

        $activeCount = $elections->filter(fn (Election $election) => $election->electionSetting?->is_active)->count();
        $completedCount = $elections->filter(fn (Election $election) => $election->electionSetting?->hasEnded())->count() + $archives->count();
        $approvedVoters = $elections->sum(fn (Election $election) => $election->users->where('status', 'approved')->count());
        $votesCast = $elections->sum(fn (Election $election) => $election->votes->count());
        $participation = $approvedVoters > 0 ? round(($votesCast / max($approvedVoters * 2, 1)) * 100, 2) : 0;

        $electionRows = $elections->map(function (Election $election) {
            $approvedVoters = $election->users->where('status', 'approved')->count();
            $votesCast = $election->votes->count();
            $turnout = $approvedVoters > 0 ? round(($votesCast / max($approvedVoters * 2, 1)) * 100, 2) : 0;

            return [
                $this->electionLabel($election),
                $this->electionTitleForElection($election),
                $election->electionSetting?->is_active ? 'Active' : ($election->electionSetting?->hasEnded() ? 'Completed' : ($election->electionSetting?->isPaused() ? 'Paused' : 'Pending')),
                $votesCast,
                $approvedVoters,
                number_format($turnout, 2).'%',
            ];
        })->values()->all();

        return [
            'title' => 'Election Summary Report',
            'context' => $this->reportContext($selectedElection),
            'generated_at' => now()->format('M d, Y h:i A'),
            'summary' => [
                ['label' => 'Election Title', 'value' => $this->reportElectionTitleValue($selectedElection)],
                ['label' => 'Total elections conducted', 'value' => $completedCount + $activeCount],
                ['label' => 'Active elections', 'value' => $activeCount],
                ['label' => 'Completed elections', 'value' => $completedCount],
                ['label' => 'Overall participation', 'value' => number_format($participation, 2).'%'],
            ],
            'sections' => [
                [
                    'title' => 'Election Status',
                    'headers' => ['Election', 'Election Title', 'Status', 'Votes Cast', 'Approved Voters', 'Participation'],
                    'rows' => $electionRows,
                ],
                [
                    'title' => 'Archived Election Totals',
                    'headers' => ['Archived Election', 'Election Title', 'Archive Reason', 'Votes', 'Candidates', 'Deleted At'],
                    'rows' => $archives->map(fn (ElectionArchive $archive) => [
                        $archive->election_name,
                        $archive->election_title ?: 'Not set',
                        ucfirst(str_replace('_', ' ', $archive->archive_reason)),
                        $archive->total_votes,
                        $archive->candidate_count,
                        optional($archive->deleted_at)->format('M d, Y h:i A') ?? '-',
                    ])->values()->all(),
                ],
            ],
            'notes' => [
                'Participation is based on votes recorded against the total possible votes from approved voters in the selected scope.',
            ],
        ];
    }

    private function electionScope(?Election $selectedElection): Collection
    {
        return Election::query()
            ->with('place')
            ->when(auth()->user()->isHost(), fn ($query) => $query->where('host_id', auth()->id()))
            ->when($selectedElection, fn ($query) => $query->whereKey($selectedElection->id))
            ->orderBy('name')
            ->get();
    }

    private function reportContext(?Election $selectedElection): string
    {
        return 'Election Filter: '.($this->electionLabel($selectedElection) ?? 'All Elections').' | Election Title: '.$this->reportElectionTitleValue($selectedElection);
    }

    private function electionTitleForElection(?Election $election): string
    {
        return $election?->electionSetting?->election_title ?: 'Not set';
    }

    private function reportElectionTitleValue(?Election $selectedElection): string
    {
        if ($selectedElection) {
            $selectedElection->loadMissing('electionSetting');

            return $this->electionTitleForElection($selectedElection);
        }

        $titles = $this->scopedElectionQuery()
            ->with(['place', 'electionSetting'])
            ->orderBy('name')
            ->get()
            ->map(function (Election $election) {
                $title = $election->electionSetting?->election_title;

                return $title ? $this->electionLabel($election).": {$title}" : null;
            })
            ->filter()
            ->values();

        return $titles->isNotEmpty() ? $titles->implode(' | ') : 'Not set';
    }

    private function scopedElectionQuery()
    {
        return Election::query()
            ->when(auth()->user()->isHost(), fn ($query) => $query->where('host_id', auth()->id()));
    }

    private function electionLabel(?Election $election): ?string
    {
        if (! $election) {
            return null;
        }

        return $election->place?->name ? "{$election->name} - {$election->place->name}" : $election->name;
    }

    private function reportCards(): array
    {
        return [
            'election-results' => [
                'title' => 'Election Results Report',
                'description' => 'Election wise vote totals, candidate counts, winners, runner up details, and turnout percentage.',
            ],
            'voter-list' => [
                'title' => 'Voter List Report',
                'description' => 'Approved voter list plus pending and rejected voter records with contact details.',
            ],
            'candidate-report' => [
                'title' => 'Candidate Report',
                'description' => 'Candidate profile export with party, election, position, votes, and comparison table.',
            ],
            'audit-log' => [
                'title' => 'Audit Log Report',
                'description' => 'Security review export of user actions, timestamps, IP addresses, and action summaries.',
            ],
            'election-summary' => [
                'title' => 'Election Summary Report',
                'description' => 'Overall election activity, active versus completed counts, and participation snapshots.',
            ],
        ];
    }
}
