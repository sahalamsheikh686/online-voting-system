@extends('layouts.app')

@section('topbar_eyebrow', 'Admin Command Center')
@section('topbar_title', 'Election control panel')
@section('topbar_text', 'Track live election activity, manage election cards, and monitor vote progress from this dashboard.')

@section('content')
    <div class="mb-4">
        <div>
            <span class="eyebrow">Admin Dashboard</span>
            <h1 class="h2 mt-2 mb-1">Live election overview</h1>
            <p class="text-secondary mb-0">{{ $singleElectionMode ? 'Single election voting mode is active.' : 'Multiple elections are currently being tracked.' }}</p>
        </div>
    </div>

    <div class="panel-card p-4 mb-4 dashboard-filter-panel">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="eyebrow">Election Filter</span>
                <h2 class="h5 mt-2 mb-1">Choose an election</h2>
                <p class="text-secondary mb-0">Election options are placed inside the menu. The selected elections will display their election cards accordingly. ‘All Elections’ shows all cards, while ‘Hide All’ hides all cards.</p>
            </div>
            <div class="d-flex flex-column align-items-stretch align-items-md-end gap-3 dashboard-filter-wrap">
                <div class="dashboard-filter-toolbar">
                    <form action="{{ route('dashboard') }}" method="GET" class="dashboard-filter-form">
                        <div class="dropdown" data-bs-auto-close="outside">
                            <button
                                class="btn btn-outline-primary rounded-pill dropdown-toggle dashboard-filter-trigger"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                @if($showAllElections)
                                    All elections selected
                                @elseif(count($selectedElectionIds))
                                    {{ count($selectedElectionIds) }} election{{ count($selectedElectionIds) > 1 ? 's' : '' }} selected
                                @else
                                    Election menu
                                @endif
                            </button>

                            <div class="dropdown-menu p-3 dashboard-filter-menu">
                                <div class="small text-uppercase text-secondary fw-semibold mb-2">Select elections</div>

                                <div class="dashboard-filter-options">
                                    @forelse($filterElections as $filterElection)
                                        <label class="dashboard-filter-option {{ in_array($filterElection['election_id'], $selectedElectionIds, true) ? 'is-selected' : '' }}">
                                            <input
                                                type="checkbox"
                                                name="elections[]"
                                                value="{{ $filterElection['election_id'] }}"
                                                class="form-check-input mt-0"
                                                @checked(in_array($filterElection['election_id'], $selectedElectionIds, true))
                                            >
                                            <span>{{ $filterElection['label'] }}</span>
                                        </label>
                                    @empty
                                        <div class="text-secondary small">No election is available right now.</div>
                                    @endforelse
                                </div>

                                @if($filterElections->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Show Selected</button>
                                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </form>

                    <a
                        href="{{ route('dashboard', ['election' => 'all']) }}"
                        class="btn rounded-pill px-4 dashboard-filter-action {{ $showAllElections ? 'btn-primary' : 'btn-outline-primary' }}"
                    >
                        All Elections
                    </a>
                    <a
                        href="{{ route('dashboard') }}"
                        class="btn rounded-pill px-4 dashboard-filter-action {{ ! $showAllElections && ! count($selectedElectionIds) ? 'btn-secondary' : 'btn-outline-secondary' }}"
                    >
                        Hide All
                    </a>
                </div>
                <div class="text-secondary small text-md-end dashboard-filter-status">
                    @if($showAllElections)
                        All election cards are visible right now.
                    @elseif(count($selectedElectionIds))
                        Showing {{ count($selectedElectionIds) }} selected election card{{ count($selectedElectionIds) > 1 ? 's' : '' }}.
                    @else
                        No election card is visible right now.
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl">
            <div class="stat-card">
                <div class="text-secondary small">Running Elections</div>
                <div class="fs-4 fw-bold mt-2">{{ $runningElections }}</div>
                <div class="mt-3 d-flex flex-column gap-1">
                    @forelse($runningElectionDetails as $detail)
                        <div class="small text-secondary">{{ $detail['place'] ? $detail['election'].' - '.$detail['place'] : $detail['election'] }}: {{ $detail['status'] }}</div>
                    @empty
                        <div class="small text-secondary">No election is running right now.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl">
            <div class="stat-card">
                <div class="text-secondary small">Total Ballot Votes</div>
                <div class="fs-4 fw-bold mt-2">{{ $totalVotes }}</div>
                <div class="mt-3 d-flex flex-column gap-1">
                    @forelse($totalVoteDetails as $detail)
                        <div class="small text-secondary">{{ $detail['place'] ? $detail['election'].' - '.$detail['place'] : $detail['election'] }}: {{ $detail['votes'] }}</div>
                    @empty
                        <div class="small text-secondary">No ballot votes recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl">
            <div class="stat-card">
                <div class="text-secondary small">President Votes</div>
                <div class="fs-4 fw-bold mt-2">{{ $presidentVotes }}</div>
                <div class="mt-3 d-flex flex-column gap-1">
                    @forelse($presidentVoteDetails as $detail)
                        <div class="small text-secondary">{{ $detail['place'] ? $detail['election'].' - '.$detail['place'] : $detail['election'] }}: {{ $detail['votes'] }}</div>
                    @empty
                        <div class="small text-secondary">No President votes recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl">
            <div class="stat-card">
                <div class="text-secondary small">Vice President Votes</div>
                <div class="fs-4 fw-bold mt-2">{{ $vicePresidentVotes }}</div>
                <div class="mt-3 d-flex flex-column gap-1">
                    @forelse($vicePresidentVoteDetails as $detail)
                        <div class="small text-secondary">{{ $detail['place'] ? $detail['election'].' - '.$detail['place'] : $detail['election'] }}: {{ $detail['votes'] }}</div>
                    @empty
                        <div class="small text-secondary">No Vice President votes recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl">
            <div class="stat-card">
                <div class="text-secondary small">Tracked Elections</div>
                <div class="fs-5 fw-bold mt-2">{{ $totalElections }}</div>
            </div>
        </div>
    </div>

    <div class="panel-card p-4 mb-4">
        <span class="eyebrow">Analytics</span>
        <h2 class="h3 mt-2 mb-4">{{ $analyticsTitle }}</h2>

        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="text-secondary small">Approved Voters</div>
                    <div class="fs-4 fw-bold mt-2">{{ $dashboardAnalytics['summary']['approved_voters'] }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="text-secondary small">Pending Voters</div>
                    <div class="fs-4 fw-bold mt-2">{{ $dashboardAnalytics['summary']['pending_voters'] }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="text-secondary small">Rejected Voters</div>
                    <div class="fs-4 fw-bold mt-2">{{ $dashboardAnalytics['summary']['rejected_voters'] }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="text-secondary small">Overall Turnout</div>
                    <div class="fs-4 fw-bold mt-2">{{ $dashboardAnalytics['summary']['turnout_percent'] }}%</div>
                    <div class="text-secondary small mt-1">{{ $dashboardAnalytics['summary']['voted_voters'] }} voted, {{ $dashboardAnalytics['summary']['not_voted_voters'] }} not voted yet</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="analytics-chart-card h-100">
                    <div class="fw-semibold mb-3">Voter Status Split</div>
                    @if(array_sum($dashboardAnalytics['status_chart']['data']))
                        <canvas id="analyticsStatusChart" height="220"></canvas>
                    @else
                        <div class="dashboard-empty-state">No voter records yet.</div>
                    @endif
                </div>
            </div>
            <div class="col-lg-4">
                <div class="analytics-chart-card h-100">
                    <div class="fw-semibold mb-3">Turnout By Election Place</div>
                    @if(count($dashboardAnalytics['turnout_chart']['labels']))
                        <canvas id="analyticsTurnoutChart" height="220"></canvas>
                    @else
                        <div class="dashboard-empty-state">No election place data yet.</div>
                    @endif
                </div>
            </div>
            <div class="col-lg-4">
                <div class="analytics-chart-card h-100">
                    <div class="fw-semibold mb-3">Votes, Candidates &amp; Approved Voters</div>
                    @if(count($dashboardAnalytics['activity_chart']['labels']))
                        <canvas id="analyticsActivityChart" height="220"></canvas>
                    @else
                        <div class="dashboard-empty-state">No election place data yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @forelse($visibleCharts as $index => $chart)
            <div class="col-lg-6">
                <div class="panel-card h-100 p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h2 class="h5 mb-1">{{ $chart['label'] }}</h2>
                            <div class="text-secondary small">{{ $chart['election_title'] ?: 'No election title set yet' }}</div>
                            <div class="text-secondary small">Positions tracked: {{ count($chart['positions']) }}</div>
                            <div class="text-secondary small mt-1">Started at: {{ $chart['started_at'] ?? 'Not started yet' }}</div>
                            @if($chart['is_scheduled'])
                                <div class="text-secondary small mt-1 text-primary fw-semibold">Scheduled to start: {{ $chart['scheduled_start_at'] }}</div>
                            @endif
                            <div class="text-secondary small mt-1">Ends at: {{ $chart['is_active'] || $chart['has_ended'] || $chart['is_scheduled'] ? ($chart['ends_at'] ?? 'Not scheduled') : 'Not scheduled' }}</div>
                        </div>
                        <div class="text-end">
                            <span class="badge text-bg-primary rounded-pill mb-2">Votes: {{ $chart['total_votes'] }}</span>
                            <div class="d-flex flex-column gap-2 election-action-stack">
                                <button
                                    class="btn btn-sm btn-success w-100"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#startElectionForm{{ $index }}"
                                    aria-expanded="false"
                                    aria-controls="startElectionForm{{ $index }}"
                                >
                                    Start Election
                                </button>
                                @if($chart['is_active'])
                                    <form action="{{ route('dashboard.pause-election', $chart['election_id']) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-sm btn-warning w-100">Pause</button>
                                    </form>
                                @elseif($chart['is_paused'])
                                    <form action="{{ route('dashboard.resume-election', $chart['election_id']) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-sm btn-primary w-100">Play</button>
                                    </form>
                                @endif
                                <form action="{{ route('dashboard.destroy-election', $chart['election_id']) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Delete this election card and archive its data?')">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="collapse mb-3" id="startElectionForm{{ $index }}">
                        <form action="{{ route('dashboard.start-election', $chart['election_id']) }}" method="POST" class="start-election-form">
                            @csrf
                            <div class="row g-2 align-items-end">
                                <div class="col-12">
                                    <label class="form-label small fw-semibold mb-1">Election Title</label>
                                    <input
                                        type="text"
                                        name="election_title"
                                        class="form-control"
                                        value="{{ old('election_title', $chart['election_title'] ?? '') }}"
                                        maxlength="120"
                                        placeholder="Example: Makwanpur Student Election 2026"
                                        required
                                    >
                                    <div class="text-secondary small mt-1">Letters, numbers, and spaces only.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold mb-1">Election Start Time (optional)</label>
                                    <input
                                        type="datetime-local"
                                        name="starts_at"
                                        class="form-control"
                                        value="{{ old('starts_at', $chart['starts_at_input'] ?? '') }}"
                                        min="{{ now()->format('Y-m-d\TH:i') }}"
                                    >
                                    <div class="text-secondary small mt-1">Leave blank to start right now. Set a future time to auto-start later.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold mb-1">Election End Time</label>
                                    <input
                                        type="datetime-local"
                                        name="ends_at"
                                        class="form-control"
                                        value="{{ old('ends_at', $chart['ends_at_input'] ?? '') }}"
                                        min="{{ now()->format('Y-m-d\TH:i') }}"
                                        required
                                    >
                                </div>
                                <div class="col-12 d-grid">
                                    <button class="btn btn-primary">Save & Run</button>
                                </div>
                            </div>
                            <div class="text-secondary small mt-2">Start time दिएमा त्यही समयमा election आफैं start हुन्छ, अनि end time सकिएपछि आफैं end भएर winner result देखिन्छ.</div>
                        </form>
                    </div>

                    <div class="dashboard-timer-card mb-3">
                        <div class="small text-uppercase text-secondary fw-semibold">Live Countdown</div>
                        <div
                            class="dashboard-countdown"
                            data-election-countdown
                            data-target="{{ $chart['is_active'] ? ($chart['countdown_target'] ?? '') : ($chart['is_scheduled'] ? ($chart['schedule_countdown_target'] ?? '') : '') }}"
                            data-paused-seconds="{{ $chart['is_paused'] ? ($chart['remaining_seconds'] ?? '') : '' }}"
                            data-reload-on-end="{{ $chart['is_active'] || $chart['is_scheduled'] ? 'true' : 'false' }}"
                        >
                            {{ $chart['is_active'] ? 'Loading countdown...' : ($chart['is_scheduled'] ? 'Loading countdown...' : ($chart['is_paused'] ? 'Election paused' : ($chart['has_ended'] ? 'Election ended' : 'Voting Coming Soon'))) }}
                        </div>
                    </div>

                    <div class="small fw-semibold mb-3 {{ $chart['status_class'] }}">
                        Status: {{ $chart['status_label'] }}
                    </div>

                    @if(count($chart['positions']))
                        <div class="d-flex flex-column gap-4">
                            @foreach($chart['positions'] as $positionIndex => $positionChart)
                                <div>
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <div>
                                            <div class="fw-semibold">{{ $positionChart['position'] }}</div>
                                            <div class="text-secondary small">
                                                @if($chart['has_ended'] && $positionChart['is_tie'])
                                                    Draw: {{ implode(', ', $positionChart['tied_candidates']) }}
                                                @elseif($chart['has_ended'] && $positionChart['winner'])
                                                    Winner: {{ $positionChart['winner']['name'] }}
                                                @else
                                                    Leader: {{ $positionChart['leader'] }}
                                                @endif
                                            </div>
                                        </div>
                                        <span class="badge text-bg-light border">Votes: {{ $positionChart['total_votes'] }}</span>
                                    </div>

                                    @if(count($positionChart['labels']))
                                        <canvas id="electionChart{{ $index }}Position{{ $positionIndex }}" height="160"></canvas>
                                    @else
                                        <div class="dashboard-empty-state">
                                            No candidates yet for {{ $positionChart['position'] }} in {{ $chart['label'] }}.
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="dashboard-empty-state">
                            No candidates yet for {{ $chart['label'] }}. Add candidates first, then the chart will appear here.
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="dashboard-empty-state">
                    Select one or more elections from the menu above to view their election cards, or use All Elections to show every election at once.
                </div>
            </div>
        @endforelse

        @if($visibleCharts->isNotEmpty())
            <div class="col-12">
                <div class="panel-card p-4">
                    <span class="eyebrow">Election Winners</span>
                    <h2 class="h3 mt-2 mb-4">Final winner overview</h2>
                    <div class="row g-4">
                        @foreach($visibleCharts as $chart)
                            <div class="col-md-6 col-xl-4">
                                <div class="winner-tile h-100">
                                    <div class="small text-uppercase text-secondary fw-semibold">{{ $chart['label'] }}</div>
                                    <div class="mt-3 d-flex flex-column gap-3">
                                        @foreach($chart['winner_summary'] as $winner)
                                            <div>
                                                <div class="small text-secondary fw-semibold">{{ $winner['position'] }}</div>
                                                <h3 class="h5 mb-1">
                                                    @if(! $chart['has_ended'])
                                                        Result pending
                                                    @elseif($winner['is_tie'])
                                                        Draw Vote
                                                    @else
                                                        {{ $winner['winner'] ?? 'No winner yet' }}
                                                    @endif
                                                </h3>
                                                <p class="text-secondary mb-0">
                                                    @if($chart['has_ended'] && $winner['is_tie'])
                                                        Draw between {{ implode(', ', $winner['tied_candidates']) }} with {{ $winner['tie_votes'] }} votes each.
                                                    @elseif($chart['has_ended'] && $winner['winner'])
                                                        Winner votes: {{ $winner['winner_votes'] }}
                                                    @elseif($chart['is_active'])
                                                        Election is still running.
                                                    @else
                                                        Result will appear after the election ends.
                                                    @endif
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="panel-card p-4">
                <span class="eyebrow">Deleted Elections</span>
                <h2 class="h3 mt-2 mb-4">Archived election records</h2>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Election</th>
                                <th>Election Title</th>
                                <th>Reason</th>
                                <th>Votes</th>
                                <th>Election Date</th>
                                <th>Deleted At</th>
                                <th>Winners</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($electionArchives as $archive)
                                <tr>
                                    <td>{{ $archive->election_name }}</td>
                                    <td>{{ $archive->election_title ?: 'Not set' }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $archive->archive_reason)) }}</td>
                                    <td>{{ $archive->total_votes }}</td>
                                    <td>{{ $archive->election_started_at?->format('M d, Y h:i A') ?? 'Not started' }} to {{ $archive->election_ended_at?->format('M d, Y h:i A') ?? 'Not ended' }}</td>
                                    <td>{{ $archive->deleted_at?->format('M d, Y h:i A') }}</td>
                                    <td>
                                        @if($archive->winners)
                                            @foreach($archive->winners as $winner)
                                                <div class="small">
                                                    {{ $winner['position'] }}:
                                                    @if($winner['is_tie'] ?? false)
                                                        Draw between {{ implode(', ', $winner['tied_candidates'] ?? []) }}
                                                    @else
                                                        {{ $winner['winner']['name'] ?? 'No winner' }}
                                                    @endif
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-secondary small">No winner snapshot</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('dashboard.restore-election', $archive) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" onclick="return confirm('Restore this election card with candidates and reset vote count to 0?')">Restore</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-secondary py-4">No deleted election records yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="panel-card p-4">
                <span class="eyebrow">Deleted Candidates</span>
                <h2 class="h3 mt-2 mb-4">Candidate delete history</h2>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Election</th>
                                <th>Position</th>
                                <th>Votes</th>
                                <th>Reason</th>
                                <th>Election Date</th>
                                <th>Deleted At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deletedCandidates as $deletedCandidate)
                                <tr>
                                    <td>{{ $deletedCandidate->candidate_name }}</td>
                                    <td>{{ $deletedCandidate->election_name }}</td>
                                    <td>{{ $deletedCandidate->position }}</td>
                                    <td>{{ $deletedCandidate->vote_count }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $deletedCandidate->deleted_reason)) }}</td>
                                    <td>{{ $deletedCandidate->election_started_at?->format('M d, Y h:i A') ?? 'Not started' }} to {{ $deletedCandidate->election_ended_at?->format('M d, Y h:i A') ?? 'Not ended' }}</td>
                                    <td>{{ $deletedCandidate->deleted_at?->format('M d, Y h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-secondary py-4">No deleted candidate records yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const dashboardAnalytics = @json($dashboardAnalytics);
        const electionCharts = @json($visibleCharts);

        const analyticsStatusCtx = document.getElementById('analyticsStatusChart');
        if (analyticsStatusCtx) {
            new Chart(analyticsStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: dashboardAnalytics.status_chart.labels,
                    datasets: [{
                        data: dashboardAnalytics.status_chart.data,
                        backgroundColor: ['#20c997', '#f59f00', '#dc3545'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        const analyticsTurnoutCtx = document.getElementById('analyticsTurnoutChart');
        if (analyticsTurnoutCtx) {
            new Chart(analyticsTurnoutCtx, {
                type: 'bar',
                data: {
                    labels: dashboardAnalytics.turnout_chart.labels,
                    datasets: [{
                        label: 'Turnout %',
                        data: dashboardAnalytics.turnout_chart.data,
                        backgroundColor: '#0d6efd',
                        borderRadius: 10,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, max: 100, ticks: { callback: (value) => `${value}%` } }
                    }
                }
            });
        }

        const analyticsActivityCtx = document.getElementById('analyticsActivityChart');
        if (analyticsActivityCtx) {
            new Chart(analyticsActivityCtx, {
                type: 'bar',
                data: {
                    labels: dashboardAnalytics.activity_chart.labels,
                    datasets: [
                        {
                            label: 'Votes',
                            data: dashboardAnalytics.activity_chart.votes,
                            backgroundColor: '#0d6efd',
                            borderRadius: 8,
                            borderSkipped: false
                        },
                        {
                            label: 'Candidates',
                            data: dashboardAnalytics.activity_chart.candidates,
                            backgroundColor: '#20c997',
                            borderRadius: 8,
                            borderSkipped: false
                        },
                        {
                            label: 'Approved Voters',
                            data: dashboardAnalytics.activity_chart.approved_voters,
                            backgroundColor: '#f59f00',
                            borderRadius: 8,
                            borderSkipped: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }

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
                    element.textContent = `Paused at ${formatCountdown(pausedSeconds * 1000)}`;
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

                element.textContent = formatCountdown(distance);
            };

            updateCountdown();
            window.setInterval(updateCountdown, 1000);
        });

        electionCharts.forEach((chart, index) => {
            chart.positions.forEach((positionChart, positionIndex) => {
                const ctx = document.getElementById(`electionChart${index}Position${positionIndex}`);
                if (!ctx) return;

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: positionChart.labels,
                        datasets: [{
                            label: `${positionChart.position} Votes`,
                            data: positionChart.votes,
                            backgroundColor: ['#0d6efd', '#20c997', '#f59f00', '#dc3545', '#6f42c1', '#ff7a59', '#14b8a6', '#7c3aed'],
                            hoverBackgroundColor: ['#3d8bfd', '#43d9b1', '#ffbf47', '#ef6b7b', '#8b5cf6', '#ff9a76', '#2dd4bf', '#a78bfa'],
                            borderRadius: 12,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `Total votes: ${context.raw}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } }
                        }
                    }
                });
            });
        });
    </script>
@endpush
