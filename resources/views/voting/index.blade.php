@extends('layouts.app')

@section('content')
    <div class="vote-shell">
        <div class="vote-hero panel-card p-4 p-lg-5 mb-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="eyebrow">Voting Portal</span>
                    @if($setting->election_title)
                        <div class="small fw-semibold text-primary mt-2">Election Title: {{ $setting->election_title }}</div>
                    @endif
                    <h1 class="vote-title mt-2 mb-2">Cast your ballot with confidence, {{ $user->name }}</h1>
                    <p class="text-secondary mb-0">Election ballot for {{ $election?->name ?? 'Not assigned' }}{{ $election?->place?->name ? ' - '.$election->place->name : '' }}. Select exactly one <strong>President</strong> and one <strong>Vice President</strong>, then submit both votes together.</p>
                </div>
                <div class="col-lg-4">
                    <div class="vote-status-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="vote-status-label">Election Status</span>
                            <span class="badge rounded-pill {{ $setting->is_active ? 'text-bg-success' : ($setting->isPaused() ? 'text-bg-warning' : ($setting->ended_at ? 'text-bg-danger' : ($setting->isScheduled() ? 'text-bg-primary' : 'text-bg-secondary'))) }}">{{ $setting->is_active ? 'Active' : ($setting->isPaused() ? 'Paused' : ($setting->ended_at ? 'Ended' : ($setting->isScheduled() ? 'Scheduled' : 'Pending'))) }}</span>
                        </div>
                        <div class="small text-secondary mb-2">Your ballot uses the secure password login already configured in this system.</div>
                        <div
                            class="vote-countdown"
                            data-election-countdown
                            data-target="{{ $setting->is_active ? ($countdownTarget ?? '') : ($setting->isScheduled() ? ($scheduleCountdownTarget ?? '') : '') }}"
                            data-paused-seconds="{{ $setting->isPaused() ? ($setting->remaining_seconds ?? '') : '' }}"
                            data-reload-on-end="{{ $setting->is_active || $setting->isScheduled() ? 'true' : 'false' }}"
                        >
                            {{ $setting->is_active ? 'Loading countdown...' : ($setting->isScheduled() ? 'Loading countdown...' : ($setting->isPaused() ? 'Voting paused' : ($setting->ended_at ? 'Election ended' : 'Voting Coming Soon'))) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(! $setting->is_active && $setting->ended_at)
            <div class="panel-card p-4 p-lg-5">
                <span class="eyebrow">Final Result</span>
                @if($setting->election_title)
                    <div class="small fw-semibold text-primary mt-2">Election Title: {{ $setting->election_title }}</div>
                @endif
                <h2 class="h3 mt-2 mb-4">Election result for {{ $election?->name }}{{ $election?->place?->name ? ' - '.$election->place->name : '' }}</h2>
                <div class="row g-4">
                    @foreach([
                        'President' => $presidentResult,
                        'Vice President' => $vicePresidentResult,
                    ] as $position => $result)
                        <div class="col-md-6">
                            <div class="winner-card h-100">
                                <div class="small text-uppercase text-secondary fw-semibold">{{ $position }}</div>
                                @if($result['is_tie'])
                                    <h3 class="h4 mt-4 mb-2">Draw Vote</h3>
                                    <p class="text-secondary mb-3">
                                        Draw vote between
                                        {{ $result['tied_candidates']->pluck('name')->implode(', ') }}
                                        with {{ $result['tie_votes'] }} votes each for {{ $position }} in {{ $election?->name }}.
                                    </p>
                                @elseif($result['winner'])
                                    @php($winner = $result['winner'])
                                    <div class="result-avatar mt-4 mb-3">
                                        @if($winner->image_path)
                                            <img src="{{ asset('storage/'.$winner->image_path) }}" alt="{{ $winner->name }}" class="result-avatar-img">
                                        @else
                                            <span>{{ strtoupper(substr($winner->name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <h3 class="h4 mb-1">{{ $winner->name }}</h3>
                                    <p class="text-secondary mb-3">This candidate won with the highest votes for {{ $position }} in {{ $election?->name }}.</p>

                                    @if($winner->vision_path)
                                        <a href="{{ asset('storage/'.$winner->vision_path) }}" target="_blank" class="btn btn-outline-primary btn-sm mb-3">View Vision</a>
                                    @endif

                                    <div class="winner-congrats">
                                        Congratulation {{ $winner->name }} for new winner of {{ $position }} in {{ $election?->name }} election.
                                    </div>
                                @else
                                    <p class="text-secondary mt-4 mb-0">No winner data is available yet.</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($setting->isPaused())
            <div class="panel-card p-4 p-lg-5">
                <span class="eyebrow">Voting Paused</span>
                <h2 class="h3 mt-2 mb-2">Voting is paused for {{ $election?->name }}{{ $election?->place?->name ? ' - '.$election->place->name : '' }}</h2>
                <p class="text-secondary mb-0">Admin ले फेरि Play गरेपछि यही remaining time बाट voting resume हुन्छ.</p>
            </div>
        @elseif(! $setting->is_active)
            <div class="panel-card p-4 p-lg-5">
                <span class="eyebrow">Voting Coming Soon</span>
                <h2 class="h3 mt-2 mb-2">Voting has not started for {{ $election?->name }}{{ $election?->place?->name ? ' - '.$election->place->name : '' }}</h2>
                @if($setting->isScheduled())
                    <p class="text-secondary mb-0">Voting will start automatically at {{ $setting->scheduled_start_at->format('M d, Y h:i A') }}.</p>
                @else
                    <p class="text-secondary mb-0">Admin ले Start Election गरेर time set गरेपछि मात्रै candidate देखिन्छ र vote दिन मिल्छ.</p>
                @endif
            </div>
        @else
            @if($user->hasVoted())
                <div class="alert alert-info rounded-4 border-0 shadow-sm">You have already cast both votes. President selection: {{ $userVotes->get('President')?->candidate?->name ?? 'Saved' }}, Vice President selection: {{ $userVotes->get('Vice President')?->candidate?->name ?? 'Saved' }}.</div>
            @endif

            <form action="{{ route('vote.store') }}" method="POST" id="ballotForm">
                @csrf
                <div class="row g-4">
                    <div class="col-xl-8">
                        <div class="position-board panel-card p-4 mb-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <div>
                                    <span class="eyebrow">Position 01</span>
                                    <h2 class="h3 mt-2 mb-1">President</h2>
                                </div>
                                <div class="small text-secondary">Choose one candidate</div>
                            </div>

                            <div class="row g-4">
                                @forelse($presidentCandidates as $candidate)
                                    <div class="col-md-6">
                                        <label class="ballot-card">
                                            <input type="radio" name="president_candidate_id" value="{{ $candidate->id }}" class="ballot-input" @checked(old('president_candidate_id') == $candidate->id) @disabled($user->hasVoted())>
                                            <span class="ballot-card-inner">
                                                <span class="candidate-card-image ballot-image">
                                                    @if($candidate->image_path)
                                                        <img src="{{ asset('storage/'.$candidate->image_path) }}" alt="{{ $candidate->name }}">
                                                    @else
                                                        <span class="candidate-image-placeholder">{{ strtoupper(substr($candidate->name, 0, 1)) }}</span>
                                                    @endif
                                                </span>
                                                <span class="ballot-body">
                                                    <span class="h4 mb-1 d-block">{{ $candidate->name }}</span>
                                                    <span class="text-secondary d-block mb-3">{{ $candidate->position }}</span>
                                                    <span class="d-flex gap-2">
                                                        @if($candidate->vision_path)
                                                            <a href="{{ asset('storage/'.$candidate->vision_path) }}" target="_blank" class="btn btn-outline-primary btn-sm flex-grow-1">Vision</a>
                                                        @else
                                                            <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1" disabled>Vision</button>
                                                        @endif
                                                        <span class="btn btn-light btn-sm flex-grow-1 ballot-select-label">Select</span>
                                                    </span>
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="text-secondary">No President candidates available for your election.</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="position-board panel-card p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <div>
                                    <span class="eyebrow">Position 02</span>
                                    <h2 class="h3 mt-2 mb-1">Vice President</h2>
                                </div>
                                <div class="small text-secondary">Choose one candidate</div>
                            </div>

                            <div class="row g-4">
                                @forelse($vicePresidentCandidates as $candidate)
                                    <div class="col-md-6">
                                        <label class="ballot-card">
                                            <input type="radio" name="vice_president_candidate_id" value="{{ $candidate->id }}" class="ballot-input" @checked(old('vice_president_candidate_id') == $candidate->id) @disabled($user->hasVoted())>
                                            <span class="ballot-card-inner">
                                                <span class="candidate-card-image ballot-image">
                                                    @if($candidate->image_path)
                                                        <img src="{{ asset('storage/'.$candidate->image_path) }}" alt="{{ $candidate->name }}">
                                                    @else
                                                        <span class="candidate-image-placeholder">{{ strtoupper(substr($candidate->name, 0, 1)) }}</span>
                                                    @endif
                                                </span>
                                                <span class="ballot-body">
                                                    <span class="h4 mb-1 d-block">{{ $candidate->name }}</span>
                                                    <span class="text-secondary d-block mb-3">{{ $candidate->position }}</span>
                                                    <span class="d-flex gap-2">
                                                        @if($candidate->vision_path)
                                                            <a href="{{ asset('storage/'.$candidate->vision_path) }}" target="_blank" class="btn btn-outline-primary btn-sm flex-grow-1">Vision</a>
                                                        @else
                                                            <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1" disabled>Vision</button>
                                                        @endif
                                                        <span class="btn btn-light btn-sm flex-grow-1 ballot-select-label">Select</span>
                                                    </span>
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="text-secondary">No Vice President candidates available for your election.</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="panel-card p-4 ballot-summary-card">
                            <span class="eyebrow">Vote Summary</span>
                            <h2 class="h4 mt-2 mb-3">Confirm your ballot</h2>
                            <div class="summary-line">
                                <span>President</span>
                                <strong id="presidentSummary">{{ $userVotes->get('President')?->candidate?->name ?? 'Not selected' }}</strong>
                            </div>
                            <div class="summary-line">
                                <span>Vice President</span>
                                <strong id="vicePresidentSummary">{{ $userVotes->get('Vice President')?->candidate?->name ?? 'Not selected' }}</strong>
                            </div>
                            <div class="guidance-note mt-4">
                                Security reminder:
                                Your account already passed password verification. This ballot will submit both positions together.
                            </div>
                            <div class="d-grid mt-4">
                                <button class="btn btn-primary btn-lg" @disabled($user->hasVoted())>Cast Vote</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        const presidentInputs = document.querySelectorAll('input[name="president_candidate_id"]');
        const vicePresidentInputs = document.querySelectorAll('input[name="vice_president_candidate_id"]');
        const presidentSummary = document.getElementById('presidentSummary');
        const vicePresidentSummary = document.getElementById('vicePresidentSummary');

        const syncSummary = (inputs, output) => {
            const selected = [...inputs].find((input) => input.checked);
            if (!output) return;
            if (!selected) return;
            output.textContent = selected.closest('.ballot-card').querySelector('.h4').textContent.trim();
        };

        presidentInputs.forEach((input) => input.addEventListener('change', () => syncSummary(presidentInputs, presidentSummary)));
        vicePresidentInputs.forEach((input) => input.addEventListener('change', () => syncSummary(vicePresidentInputs, vicePresidentSummary)));

        syncSummary(presidentInputs, presidentSummary);
        syncSummary(vicePresidentInputs, vicePresidentSummary);

        const formatCountdown = (distance) => {
            const totalSeconds = Math.max(Math.floor(distance / 1000), 0);
            const days = Math.floor(totalSeconds / 86400);
            const hours = Math.floor((totalSeconds % 86400) / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            const hh = String(hours).padStart(2, '0');
            const mm = String(minutes).padStart(2, '0');
            const ss = String(seconds).padStart(2, '0');

            return days > 0 ? `${days}d ${hh}:${mm}:${ss}` : `${hh}:${mm}:${ss}`;
        };

        document.querySelectorAll('[data-election-countdown]').forEach((element) => {
            const target = element.dataset.target;
            const pausedSeconds = Number(element.dataset.pausedSeconds || 0);
            const reloadOnEnd = element.dataset.reloadOnEnd === 'true';

            if (!target && !pausedSeconds) {
                return;
            }

            const updateCountdown = () => {
                if (!target && pausedSeconds > 0) {
                    element.textContent = `Voting paused at ${formatCountdown(pausedSeconds * 1000)}`;
                    return;
                }

                const distance = new Date(target).getTime() - Date.now();

                if (distance <= 0) {
                    element.textContent = 'Election ended';

                    if (reloadOnEnd && !element.dataset.reloaded) {
                        element.dataset.reloaded = 'true';
                        window.setTimeout(() => window.location.reload(), 1200);
                    }

                    return;
                }

                element.textContent = `Time remaining: ${formatCountdown(distance)}`;
            };

            updateCountdown();
            window.setInterval(updateCountdown, 1000);
        });
    </script>
@endpush
