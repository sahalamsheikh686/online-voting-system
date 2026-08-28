<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\ElectionSetting;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VoteController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = auth()->user();
        $setting = ElectionSetting::query()->firstOrCreate(['election_id' => $user->election_id], [
            'is_active' => false,
            'started_at' => null,
            'ends_at' => null,
        ]);

        $setting = $this->syncElectionStatus($setting);

        $election = $user->election()->with([
            'place',
            'candidates' => fn ($query) => $query->orderBy('name')->with('votes'),
        ])->first();
        $candidates = $election?->candidates ?? collect();
        $presidentCandidates = $candidates->filter(fn (Candidate $candidate) => strcasecmp($candidate->position, 'President') === 0)->values();
        $vicePresidentCandidates = $candidates->filter(fn (Candidate $candidate) => strcasecmp($candidate->position, 'Vice President') === 0)->values();

        $presidentResult = $this->buildPositionResult($presidentCandidates);
        $vicePresidentResult = $this->buildPositionResult($vicePresidentCandidates);

        $userVotes = $user->votes()
            ->with('candidate')
            ->get()
            ->keyBy('position');

        return view('voting.index', [
            'user' => $user,
            'election' => $election,
            'presidentCandidates' => $presidentCandidates,
            'vicePresidentCandidates' => $vicePresidentCandidates,
            'setting' => $setting,
            'presidentResult' => $presidentResult,
            'vicePresidentResult' => $vicePresidentResult,
            'userVotes' => $userVotes,
            'countdownTarget' => optional($setting->ends_at)?->toIso8601String(),
            'scheduleCountdownTarget' => $setting->isScheduled() ? optional($setting->scheduled_start_at)?->toIso8601String() : null,
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $setting = ElectionSetting::query()->firstOrCreate(['election_id' => $user->election_id]);
        $setting = $this->syncElectionStatus($setting);

        $validated = request()->validate([
            'president_candidate_id' => ['required', 'different:vice_president_candidate_id', 'exists:candidates,id'],
            'vice_president_candidate_id' => ['required', 'different:president_candidate_id', 'exists:candidates,id'],
        ], [
            'president_candidate_id.required' => 'Please select a President candidate.',
            'vice_president_candidate_id.required' => 'Please select a Vice President candidate.',
            'president_candidate_id.different' => 'President and Vice President selections must be different.',
            'vice_president_candidate_id.different' => 'President and Vice President selections must be different.',
        ]);

        if ($setting->hasEnded()) {
            return back()->withErrors(['vote' => 'The election has ended. Voting is now closed.']);
        }

        if (! $setting->is_active) {
            return back()->withErrors(['vote' => $setting->isPaused()
                ? 'Voting is currently paused for your election.'
                : 'Voting has not started for your election yet.']);
        }

        if ($user->hasVoted()) {
            return back()->withErrors(['vote' => 'You have already cast your vote.']);
        }

        $presidentCandidate = Candidate::query()->findOrFail($validated['president_candidate_id']);
        $vicePresidentCandidate = Candidate::query()->findOrFail($validated['vice_president_candidate_id']);

        if (
            (int) $user->election_id !== (int) $presidentCandidate->election_id ||
            (int) $user->election_id !== (int) $vicePresidentCandidate->election_id
        ) {
            abort(403);
        }

        if ($presidentCandidate->position !== 'President' || $vicePresidentCandidate->position !== 'Vice President') {
            abort(403);
        }

        DB::transaction(function () use ($user, $presidentCandidate, $vicePresidentCandidate) {
            Vote::create([
                'user_id' => $user->id,
                'election_id' => $presidentCandidate->election_id,
                'candidate_id' => $presidentCandidate->id,
                'position' => 'President',
            ]);

            Vote::create([
                'user_id' => $user->id,
                'election_id' => $vicePresidentCandidate->election_id,
                'candidate_id' => $vicePresidentCandidate->id,
                'position' => 'Vice President',
            ]);

            $user->update([
                'has_voted_at' => now(),
            ]);
        });

        return back()->with('status', 'Your President and Vice President votes have been recorded successfully.');
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

    private function buildPositionResult(Collection $candidates): array
    {
        $winner = $this->resolveWinner($candidates);
        $topVotes = $candidates->map(fn (Candidate $candidate) => $candidate->votes->count())->max() ?? 0;
        $tiedCandidates = $topVotes > 0
            ? $candidates
                ->filter(fn (Candidate $candidate) => $candidate->votes->count() === $topVotes)
                ->sortBy('name')
                ->values()
            : collect();

        return [
            'winner' => $tiedCandidates->count() > 1 ? null : $winner,
            'is_tie' => $tiedCandidates->count() > 1,
            'tie_votes' => $tiedCandidates->count() > 1 ? $topVotes : 0,
            'tied_candidates' => $tiedCandidates,
        ];
    }
}
