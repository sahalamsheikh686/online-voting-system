@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <span class="eyebrow">Reports</span>
            <h1 class="h2 mt-2 mb-1">Export election reports</h1>
            <p class="text-secondary mb-0">Choose an election filter first, then download each report in PDF or Excel format.</p>
        </div>
    </div>

    <div class="panel-card p-4 mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-lg-8">
                <label for="reportElectionFilter" class="form-label">Election Filter</label>
                <select id="reportElectionFilter" class="form-select">
                    <option value="">Select election first</option>
                    <option value="all">All Elections</option>
                    @foreach($elections as $election)
                        <option value="{{ $election->id }}">{{ $election->name }}{{ $election->place?->name ? ' - '.$election->place->name : '' }}</option>
                    @endforeach
                </select>
                <div class="form-text">Election select गरेपछि report export buttons active हुन्छन्.</div>
            </div>
            <div class="col-lg-4">
                <div class="report-filter-status small text-secondary" data-report-filter-label>No election selected yet.</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @foreach($reportCards as $key => $report)
            <div class="col-xl-6">
                <div class="panel-card p-4 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <span class="eyebrow">Report Type</span>
                        <h2 class="h4 mt-2 mb-2">{{ $report['title'] }}</h2>
                        <p class="text-secondary mb-0">{{ $report['description'] }}</p>
                    </div>

                    <div class="mt-auto d-flex flex-wrap gap-2">
                        <a
                            href="#"
                            class="btn btn-primary rounded-pill px-4 disabled"
                            aria-disabled="true"
                            data-report-link
                            data-report-type="{{ $key }}"
                            data-report-format="pdf"
                        >
                            Export PDF
                        </a>
                        <a
                            href="#"
                            class="btn btn-outline-primary rounded-pill px-4 disabled"
                            aria-disabled="true"
                            data-report-link
                            data-report-type="{{ $key }}"
                            data-report-format="excel"
                        >
                            Export Excel
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        const electionFilter = document.getElementById('reportElectionFilter');
        const reportLinks = document.querySelectorAll('[data-report-link]');
        const reportFilterLabel = document.querySelector('[data-report-filter-label]');

        const updateReportLinks = () => {
            const election = electionFilter?.value ?? '';
            const selectedLabel = electionFilter?.selectedOptions?.[0]?.textContent?.trim() ?? '';
            const enabled = election !== '';

            if (reportFilterLabel) {
                reportFilterLabel.textContent = enabled
                    ? `Selected filter: ${selectedLabel}`
                    : 'No election selected yet.';
            }

            reportLinks.forEach((link) => {
                if (!enabled) {
                    link.href = '#';
                    link.classList.add('disabled');
                    link.setAttribute('aria-disabled', 'true');
                    return;
                }

                const reportType = link.dataset.reportType;
                const format = link.dataset.reportFormat;
                const url = new URL(`{{ url('/reports') }}/${reportType}/${format}`);
                url.searchParams.set('election', election);
                link.href = url.toString();
                link.classList.remove('disabled');
                link.setAttribute('aria-disabled', 'false');
            });
        };

        electionFilter?.addEventListener('change', updateReportLinks);
        updateReportLinks();
    </script>
@endpush
