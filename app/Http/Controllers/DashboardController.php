<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\DeletedCandidate;
use App\Models\Election;
use App\Models\ElectionArchive;
use App\Models\ElectionSetting;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $electionFilter = request()->query('election');
        $showAllElections = $electionFilter === 'all';
        $selectedElectionIds = collect(request()->query('elections', []))
            ->filter(fn ($electionId) => is_scalar($electionId) && is_numeric($electionId))
            ->map(fn ($electionId) => (int) $electionId)
            ->unique()
            ->values();

        if ($selectedElectionIds->isEmpty() && is_numeric($electionFilter)) {
            $selectedElectionIds = collect([(int) $electionFilter]);
        }

        $elections = Election::query()
            ->with(['place', 'candidates.votes', 'votes', 'users', 'electionSetting'])
            ->when(auth()->user()->isHost(), fn ($query) => $query->where('host_id', auth()->id()))
            ->orderBy('name')
            ->get();

        $charts = $elections->map(function (Election $election) {
            $setting = ElectionSetting::query()->firstOrCreate(
                ['election_id' => $election->id],
                [
                    'is_active' => false,
                    'started_at' => null,
                    'ends_at' => null,
                ]
            );

            $setting = $this->syncElectionStatus($setting);
            $positions = $this->buildPositionResults($election->candidates);

            return [
                'election_id' => $election->id,
                'election' => $election->name,
                'place' => $election->place?->name,
                'label' => $election->place?->name ? "{$election->name} - {$election->place->name}" : $election->name,
                'election_title' => $setting->election_title,
                'total_votes' => $election->votes->count(),
                'positions' => $positions->values(),
                'is_active' => $setting->is_active,
                'is_paused' => $setting->isPaused(),
                'is_scheduled' => $setting->isScheduled(),
                'has_ended' => $setting->ended_at !== null,
                'status_label' => $setting->is_active ? 'Running' : ($setting->isPaused() ? 'Paused' : ($setting->ended_at ? 'Ended' : ($setting->isScheduled() ? 'Scheduled' : 'Not started'))),
                'status_class' => $setting->is_active ? 'text-success' : ($setting->isPaused() ? 'text-warning' : ($setting->ended_at ? 'text-danger' : ($setting->isScheduled() ? 'text-primary' : 'text-secondary'))),
                'started_at' => optional($setting->started_at)?->format('M d, Y h:i A'),
                'scheduled_start_at' => optional($setting->scheduled_start_at)?->format('M d, Y h:i A'),
                'starts_at_input' => optional($setting->scheduled_start_at)?->format('Y-m-d\TH:i'),
                'ends_at' => optional($setting->ends_at)?->format('M d, Y h:i A'),
                'ends_at_input' => optional($setting->ends_at)?->format('Y-m-d\TH:i'),
                'countdown_target' => $setting->is_active ? optional($setting->ends_at)?->toIso8601String() : null,
                'schedule_countdown_target' => $setting->isScheduled() ? optional($setting->scheduled_start_at)?->toIso8601String() : null,
                'remaining_seconds' => $setting->remaining_seconds,
                'winner_summary' => $positions
                    ->map(fn (array $position) => [
                        'position' => $position['position'],
                        'winner' => $position['winner']['name'] ?? null,
                        'winner_votes' => $position['winner']['votes'] ?? 0,
                        'is_tie' => $position['is_tie'],
                        'tie_votes' => $position['tie_votes'],
                        'tied_candidates' => $position['tied_candidates'],
                    ])
                    ->values(),
            ];
        })->values();

        $filterElections = $charts->values();
        $visibleCharts = $showAllElections
            ? $filterElections
            : ($selectedElectionIds->isNotEmpty()
                ? $filterElections->whereIn('election_id', $selectedElectionIds)->values()
                : collect());
        $presidentVoteDetails = $charts
            ->map(function (array $chart) {
                $presidentVotes = collect($chart['positions'])
                    ->firstWhere('position', 'President')['total_votes'] ?? 0;

                return [
                'election' => $chart['election'],
                'place' => $chart['place'],
                'votes' => $presidentVotes,
                ];
            })
            ->filter(fn (array $detail) => $detail['votes'] > 0)
            ->values();
        $vicePresidentVoteDetails = $charts
            ->map(function (array $chart) {
                $vicePresidentVotes = collect($chart['positions'])
                    ->firstWhere('position', 'Vice President')['total_votes'] ?? 0;

                return [
                'election' => $chart['election'],
                'place' => $chart['place'],
                'votes' => $vicePresidentVotes,
                ];
            })
            ->filter(fn (array $detail) => $detail['votes'] > 0)
            ->values();
        $totalVoteDetails = $charts
            ->map(fn (array $chart) => [
                'election' => $chart['election'],
                'place' => $chart['place'],
                'votes' => $chart['total_votes'],
            ])
            ->filter(fn (array $detail) => $detail['votes'] > 0)
            ->values();
        $runningElectionDetails = $charts
            ->filter(fn (array $chart) => $chart['is_active'])
            ->map(fn (array $chart) => [
                'election' => $chart['election'],
                'place' => $chart['place'],
                'status' => 'Running',
            ])
            ->values();
        $dashboardAnalytics = $this->buildDashboardAnalytics($elections);

        return view('dashboard.index', [
            'charts' => $charts,
            'visibleCharts' => $visibleCharts,
            'filterElections' => $filterElections,
            'selectedElectionIds' => $selectedElectionIds->all(),
            'showAllElections' => $showAllElections,
            'singleElectionMode' => $charts->count() === 1,
            'totalVotes' => $this->scopedVoteQuery()->count(),
            'presidentVotes' => $this->scopedVoteQuery()->where('position', 'President')->count(),
            'vicePresidentVotes' => $this->scopedVoteQuery()->where('position', 'Vice President')->count(),
            'totalElections' => $charts->count(),
            'runningElections' => $charts->where('is_active', true)->count(),
            'runningElectionDetails' => $runningElectionDetails,
            'totalVoteDetails' => $totalVoteDetails,
            'presidentVoteDetails' => $presidentVoteDetails,
            'vicePresidentVoteDetails' => $vicePresidentVoteDetails,
            'dashboardAnalytics' => $dashboardAnalytics,
            'analyticsTitle' => auth()->user()->isHost() ? 'Host Analytics' : 'System Analytics',
            'electionArchives' => ElectionArchive::query()
                ->when(auth()->user()->isHost(), fn ($query) => $query->where('host_id', auth()->id()))
                ->whereNull('restored_at')
                ->latest('deleted_at')
                ->latest('id')
                ->get(),
            'deletedCandidates' => DeletedCandidate::query()
                ->when(auth()->user()->isHost(), fn ($query) => $query->whereHas('electionArchive', fn ($archiveQuery) => $archiveQuery->where('host_id', auth()->id())))
                ->whereNull('restored_at')
                ->latest('deleted_at')
                ->latest('id')
                ->get(),
        ]);
    }

    public function startElection(Election $election): RedirectResponse
    {
        $this->authorizeElectionAccess($election);

        $validated = request()->validate([
            'election_title' => ['required', 'string', 'max:120', 'regex:/^[\pL\pN ]+$/u'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['required', 'date', 'after:now'],
        ], [
            'election_title.required' => 'Please enter the election title.',
            'election_title.regex' => 'Election title can contain only letters, numbers, and spaces.',
            'ends_at.required' => 'Please choose the election end time.',
            'ends_at.after' => 'Election end time must be in the future.',
        ]);

        $endsAt = Carbon::parse($validated['ends_at']);
        $scheduledStartAt = ! empty($validated['starts_at']) ? Carbon::parse($validated['starts_at']) : null;
        $isScheduled = $scheduledStartAt !== null && $scheduledStartAt->isFuture();

        if ($isScheduled && $endsAt->lessThanOrEqualTo($scheduledStartAt)) {
            throw ValidationException::withMessages([
                'ends_at' => 'Election end time must be after the scheduled start time.',
            ]);
        }

        DB::transaction(function () use ($election, $validated, $endsAt, $scheduledStartAt, $isScheduled) {
            $election->loadMissing(['candidates.votes', 'votes', 'electionSetting', 'users']);

            $setting = ElectionSetting::query()->firstOrCreate(
                ['election_id' => $election->id],
                ['is_active' => false]
            );

            $setting = $this->syncElectionStatus($setting);

            if (
                $setting->ended_at &&
                ($election->votes->isNotEmpty() || $election->users->contains(fn (User $user) => $user->has_voted_at !== null))
            ) {
                $this->archiveElection($election, $setting, 'restarted');
                $this->resetElectionData($election);
            }

            $setting->update([
                'election_title' => trim($validated['election_title']),
                'is_active' => ! $isScheduled,
                'started_at' => $isScheduled ? null : ($setting->is_active && $setting->started_at ? $setting->started_at : now()),
                'scheduled_start_at' => $isScheduled ? $scheduledStartAt : null,
                'paused_at' => null,
                'remaining_seconds' => null,
                'ended_at' => null,
                'ends_at' => $endsAt,
            ]);
        });

        return back()->with('status', $isScheduled
            ? "Election scheduled to start automatically for {$election->name} at ".$scheduledStartAt->format('M d, Y h:i A').'.'
            : "Election started successfully for {$election->name}.");
    }

    public function pauseElection(Election $election): RedirectResponse
    {
        $this->authorizeElectionAccess($election);

        $setting = ElectionSetting::query()->firstOrCreate(['election_id' => $election->id], ['is_active' => false]);
        $setting = $this->syncElectionStatus($setting);

        if (! $setting->is_active || ! $setting->ends_at) {
            return back()->withErrors(['pause' => 'Only a running election can be paused.']);
        }

        $remainingSeconds = max(now()->diffInSeconds($setting->ends_at, false), 0);

        $setting->update([
            'is_active' => false,
            'paused_at' => now(),
            'remaining_seconds' => $remainingSeconds,
        ]);

        return back()->with('status', "Election paused for {$election->name}.");
    }

    public function resumeElection(Election $election): RedirectResponse
    {
        $this->authorizeElectionAccess($election);

        $setting = ElectionSetting::query()->firstOrCreate(['election_id' => $election->id], ['is_active' => false]);
        $setting = $this->syncElectionStatus($setting);

        if (! $setting->isPaused() || ! $setting->remaining_seconds) {
            return back()->withErrors(['resume' => 'This election is not paused.']);
        }

        $setting->update([
            'is_active' => true,
            'paused_at' => null,
            'ends_at' => now()->addSeconds($setting->remaining_seconds),
            'remaining_seconds' => null,
        ]);

        return back()->with('status', "Election resumed for {$election->name}.");
    }

    public function destroyElection(Election $election): RedirectResponse
    {
        $this->authorizeElectionAccess($election);

        DB::transaction(function () use ($election) {
            $election->loadMissing(['candidates.votes', 'votes', 'electionSetting', 'users']);
            $setting = ElectionSetting::query()->firstOrCreate(['election_id' => $election->id], ['is_active' => false]);
            $setting = $this->syncElectionStatus($setting);

            $this->archiveElection($election, $setting, 'deleted');

            User::query()
                ->where('election_id', $election->id)
                ->update([
                    'last_known_election_name' => $election->name,
                    'election_id' => null,
                    'has_voted_at' => null,
                ]);

            $election->delete();
        });

        return back()->with('status', 'Election card deleted and archived successfully.');
    }

    public function restoreElection(ElectionArchive $archive): RedirectResponse
    {
        if (auth()->user()->isHost() && (int) $archive->host_id !== (int) auth()->id()) {
            abort(403);
        }

        if (Election::query()->where('name', $archive->election_name)->where('host_id', $archive->host_id)->exists()) {
            return back()->withErrors([
                'restore' => "An election named {$archive->election_name} already exists. Delete or rename it before restoring this archive.",
            ]);
        }

        DB::transaction(function () use ($archive) {
            $election = Election::query()->create([
                'host_id' => $archive->host_id,
                'name' => $archive->election_name,
                'is_active' => true,
            ]);

            User::query()
                ->whereNull('election_id')
                ->where('last_known_election_name', $archive->election_name)
                ->update([
                    'election_id' => $election->id,
                ]);

            ElectionSetting::query()->create([
                'election_id' => $election->id,
                'election_title' => $archive->election_title,
                'is_active' => false,
                'started_at' => null,
                'ends_at' => null,
                'ended_at' => null,
            ]);

            $archive->loadMissing('deletedCandidates');

            foreach ($archive->deletedCandidates as $deletedCandidate) {
                Candidate::query()->create([
                    'election_id' => $election->id,
                    'name' => $deletedCandidate->candidate_name,
                    'age' => $deletedCandidate->age ?? 0,
                    'position' => $deletedCandidate->position ?: 'President',
                    'image_path' => $deletedCandidate->image_path,
                    'vision_path' => $deletedCandidate->vision_path,
                    'email' => $this->resolveRestoredCandidateEmail($deletedCandidate->email, $deletedCandidate->candidate_name),
                    'is_active' => true,
                ]);
            }

            $archive->deletedCandidates()->update([
                'restored_at' => now(),
            ]);

            $archive->update([
                'restored_at' => now(),
            ]);
        });

        return back()->with('status', "Election card {$archive->election_name} restored successfully. Vote count will start from 0.");
    }

    private function syncElectionStatus(ElectionSetting $setting): ElectionSetting
    {
        if (
            ! $setting->is_active
            && $setting->scheduled_start_at
            && now()->greaterThanOrEqualTo($setting->scheduled_start_at)
        ) {
            $alreadyPastEnd = $setting->ends_at && now()->greaterThanOrEqualTo($setting->ends_at);

            $setting->update([
                'started_at' => $setting->scheduled_start_at,
                'scheduled_start_at' => null,
                'is_active' => ! $alreadyPastEnd,
                'paused_at' => null,
                'remaining_seconds' => null,
                'ended_at' => $alreadyPastEnd ? $setting->ends_at : null,
            ]);

            return $setting->fresh();
        }

        if (! $setting->started_at) {
            if ($setting->is_active || $setting->ended_at) {
                $setting->update([
                    'is_active' => false,
                    'paused_at' => null,
                    'remaining_seconds' => null,
                    'ended_at' => null,
                ]);
            }

            return $setting->fresh();
        }

        if ($setting->is_active && $setting->hasEnded()) {
            $setting->update([
                'is_active' => false,
                'paused_at' => null,
                'remaining_seconds' => null,
                'ended_at' => $setting->ended_at ?: now(),
            ]);
        }

        return $setting->fresh();
    }

    private function buildPositionResults(Collection $candidates): Collection
    {
        return $candidates
            ->groupBy(fn (Candidate $candidate) => trim($candidate->position ?: 'Other'))
            ->map(function (Collection $positionCandidates, string $position) {
                $labels = $positionCandidates->pluck('name')->values();
                $voteTotals = $positionCandidates->map(fn (Candidate $candidate) => $candidate->votes->count())->values();
                $leaderIndex = $voteTotals->search($voteTotals->max());
                $maxVotes = $voteTotals->max();
                $tiedCandidates = $maxVotes > 0
                    ? $positionCandidates
                        ->filter(fn (Candidate $candidate) => $candidate->votes->count() === $maxVotes)
                        ->sortBy('name')
                        ->values()
                    : collect();
                $isTie = $tiedCandidates->count() > 1;
                $winner = $isTie ? null : $this->resolveWinner($positionCandidates);
                $tieNames = $tiedCandidates->pluck('name')->values()->all();

                return [
                    'position' => $position,
                    'labels' => $labels,
                    'votes' => $voteTotals,
                    'leader' => $isTie
                        ? 'Draw between '.implode(', ', $tieNames)
                        : ($labels[$leaderIndex] ?? 'No leader yet'),
                    'total_votes' => $voteTotals->sum(),
                    'is_tie' => $isTie,
                    'tie_votes' => $isTie ? $maxVotes : 0,
                    'tied_candidates' => $tieNames,
                    'winner' => $winner ? [
                        'name' => $winner->name,
                        'votes' => $winner->votes->count(),
                        'image_path' => $winner->image_path,
                        'vision_path' => $winner->vision_path,
                    ] : null,
                ];
            })
            ->sortBy('position')
            ->values();
    }

    private function buildDashboardAnalytics(Collection $elections): array
    {
        $voters = $elections->flatMap(fn (Election $election) => $election->users);
        $approvedVoters = $voters->where('status', 'approved')->count();
        $pendingVoters = $voters->where('status', 'pending')->count();
        $rejectedVoters = $voters->where('status', 'rejected')->count();
        $votedVoters = $voters->filter(fn (User $user) => $user->has_voted_at !== null)->count();
        $notVotedVoters = max($approvedVoters - $votedVoters, 0);
        $totalBallotVotes = $elections->sum(fn (Election $election) => $election->votes->count());

        $placeAnalytics = $elections
            ->map(function (Election $election) {
                $users = $election->users;
                $approved = $users->where('status', 'approved')->count();
                $voted = $users->filter(fn (User $user) => $user->has_voted_at !== null)->count();

                return [
                    'label' => $election->place?->name ? "{$election->name} - {$election->place->name}" : $election->name,
                    'approved_voters' => $approved,
                    'pending_voters' => $users->where('status', 'pending')->count(),
                    'rejected_voters' => $users->where('status', 'rejected')->count(),
                    'voted_voters' => $voted,
                    'turnout_percent' => $approved > 0 ? round(($voted / $approved) * 100, 1) : 0,
                    'candidates' => $election->candidates->count(),
                    'votes' => $election->votes->count(),
                ];
            })
            ->values();

        return [
            'summary' => [
                'approved_voters' => $approvedVoters,
                'pending_voters' => $pendingVoters,
                'rejected_voters' => $rejectedVoters,
                'voted_voters' => $votedVoters,
                'not_voted_voters' => $notVotedVoters,
                'total_ballot_votes' => $totalBallotVotes,
                'turnout_percent' => $approvedVoters > 0 ? round(($votedVoters / $approvedVoters) * 100, 1) : 0,
            ],
            'status_chart' => [
                'labels' => ['Approved', 'Pending', 'Rejected'],
                'data' => [$approvedVoters, $pendingVoters, $rejectedVoters],
            ],
            'turnout_chart' => [
                'labels' => $placeAnalytics->pluck('label')->all(),
                'data' => $placeAnalytics->pluck('turnout_percent')->all(),
            ],
            'activity_chart' => [
                'labels' => $placeAnalytics->pluck('label')->all(),
                'votes' => $placeAnalytics->pluck('votes')->all(),
                'candidates' => $placeAnalytics->pluck('candidates')->all(),
                'approved_voters' => $placeAnalytics->pluck('approved_voters')->all(),
            ],
            'places' => $placeAnalytics->all(),
        ];
    }

    private function resolveWinner(Collection $candidates): ?Candidate
    {
        $winner = $candidates->reduce(function (?Candidate $currentWinner, Candidate $candidate) {
            if (! $currentWinner) {
                return $candidate;
            }

            $currentVotes = $currentWinner->votes->count();
            $candidateVotes = $candidate->votes->count();

            if ($candidateVotes > $currentVotes) {
                return $candidate;
            }

            if ($candidateVotes === $currentVotes && strcmp($candidate->name, $currentWinner->name) < 0) {
                return $candidate;
            }

            return $currentWinner;
        });

        if (! $winner || $winner->votes->count() === 0) {
            return null;
        }

        return $winner;
    }

    private function archiveElection(Election $election, ElectionSetting $setting, string $reason): ElectionArchive
    {
        $election->loadMissing(['candidates.votes', 'votes']);
        $positionResults = $this->buildPositionResults($election->candidates);

        $archive = ElectionArchive::query()->create([
            'host_id' => $election->host_id,
            'election_name' => $election->name,
            'election_title' => $setting->election_title,
            'archive_reason' => $reason,
            'candidate_count' => $election->candidates->count(),
            'total_votes' => $election->votes->count(),
            'election_started_at' => $setting->started_at,
            'election_ended_at' => $setting->ended_at ?: $setting->ends_at ?: now(),
            'deleted_at' => now(),
            'winners' => $positionResults
                ->map(fn (array $position) => [
                    'position' => $position['position'],
                    'winner' => $position['winner'],
                    'is_tie' => $position['is_tie'],
                    'tie_votes' => $position['tie_votes'],
                    'tied_candidates' => $position['tied_candidates'],
                ])
                ->values()
                ->all(),
            'position_results' => $positionResults->values()->all(),
        ]);

        foreach ($election->candidates as $candidate) {
            DeletedCandidate::query()->create([
                'election_archive_id' => $archive->id,
                'original_candidate_id' => $candidate->id,
                'election_name' => $election->name,
                'candidate_name' => $candidate->name,
                'age' => $candidate->age,
                'position' => $candidate->position,
                'email' => $candidate->email,
                'image_path' => $candidate->image_path,
                'vision_path' => $candidate->vision_path,
                'vote_count' => $candidate->votes->count(),
                'deleted_reason' => $reason === 'deleted' ? 'election_deleted' : 'election_restarted',
                'election_started_at' => $setting->started_at,
                'election_ended_at' => $setting->ended_at ?: $setting->ends_at ?: now(),
                'deleted_at' => now(),
            ]);
        }

        return $archive;
    }

    private function resetElectionData(Election $election): void
    {
        Vote::query()->where('election_id', $election->id)->delete();

        User::query()
            ->where('election_id', $election->id)
            ->update(['has_voted_at' => null]);
    }

    private function resolveRestoredCandidateEmail(?string $email, string $candidateName): string
    {
        $baseEmail = $email ?: strtolower(str_replace(' ', '.', trim($candidateName))).'@restored.local';

        if (! Candidate::query()->where('email', $baseEmail)->exists()) {
            return $baseEmail;
        }

        $parts = explode('@', $baseEmail, 2);
        $local = $parts[0] ?: 'candidate';
        $domain = $parts[1] ?? 'restored.local';

        return $local.'.restored.'.now()->format('YmdHis').'@'.$domain;
    }

    private function scopedVoteQuery()
    {
        return Vote::query()
            ->when(auth()->user()->isHost(), fn ($query) => $query->whereHas('election', fn ($electionQuery) => $electionQuery->where('host_id', auth()->id())));
    }

    private function authorizeElectionAccess(Election $election): void
    {
        if (auth()->user()->isHost() && (int) $election->host_id !== (int) auth()->id()) {
            abort(403);
        }
    }
}
