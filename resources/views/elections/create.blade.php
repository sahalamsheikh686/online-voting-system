@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <div>
            <span class="eyebrow">Add Election Card</span>
            <h1 class="h2 mt-2 mb-1">Create a new election card</h1>
            <p class="text-secondary mb-0">Only new election card creation is available here.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="panel-card p-4 p-lg-5">
                <div class="mb-4">
                    <span class="eyebrow">Election Setup</span>
                    <h2 class="h4 mt-2 mb-1">Only election and place card creation lives here</h2>
                    <p class="text-secondary mb-0">New election added here will automatically appear in registration, candidate management, reports, and dashboard lists.</p>
                </div>

                <form action="{{ route('elections.store') }}" method="POST" class="row g-4 align-items-end">
                    @csrf
                    <div class="col-lg-8">
                        <label class="form-label">Election Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control form-control-lg" placeholder="Enter election name">
                    </div>
                    <div class="col-lg-8">
                        <label class="form-label">Election Title</label>
                        <input type="text" name="election_title" value="{{ old('election_title') }}" class="form-control form-control-lg" placeholder="Enter election title">
                    </div>
                    <div class="col-lg-8">
                        <label class="form-label">Election Place</label>
                        <input type="text" name="place_name" value="{{ old('place_name') }}" class="form-control form-control-lg" placeholder="Enter election place" title="Election place must contain text. Numbers only are not valid.">
                    </div>
                    <div class="col-lg-4 d-grid">
                        <button class="btn btn-primary btn-lg">Add Election Card</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-xl-10">
            <div class="panel-card p-4 p-lg-5">
                <div class="mb-4">
                    <span class="eyebrow">Election Invite Links</span>
                    <h2 class="h4 mt-2 mb-1">Share exact registration links</h2>
                    <p class="text-secondary mb-0">Each QR and link opens voter registration for that exact election and place.</p>
                </div>

                <div class="row g-3">
                    @forelse($elections as $election)
                        @php($inviteUrl = $election->invite_token ? route('register.invite', $election->invite_token) : route('register'))
                        <div class="col-md-6">
                            <div class="invite-card h-100">
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="invite-qr" style="width: 132px; height: 132px;" data-qr-text="{{ $inviteUrl }}"></div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold">{{ $election->name }}</div>
                                        <div class="text-secondary small mb-2">{{ $election->place?->name ? 'Place: '.$election->place->name : 'Place not set' }}</div>
                                        <input type="text" class="form-control form-control-sm invite-link-input" value="{{ $inviteUrl }}" readonly>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-copy-link="{{ $inviteUrl }}">Copy Link</button>
                                            <a href="{{ $inviteUrl }}" class="btn btn-sm btn-primary" target="_blank" rel="noopener">Open Invite</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="dashboard-empty-state">No election card available for invite link yet.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-xl-10">
            <div class="panel-card p-4 p-lg-5 border border-danger-subtle">
                <div class="mb-4">
                    <span class="eyebrow text-danger">Delete Added Election Card</span>
                    <h2 class="h4 mt-2 mb-1 text-danger">Permanently delete an election card</h2>
                    <p class="text-secondary mb-0">Deleting an election from here does not save it to archive. The selected election is directly removed from the system, dashboard list, reports, candidate records, vote records, and related database data.</p>
                </div>

                <form action="{{ route('elections.hard-delete') }}" method="POST" class="row g-4 align-items-end" onsubmit="return confirm('This will permanently delete the selected election card and related records from the database. Continue?')">
                    @csrf
                    @method('DELETE')
                    <div class="col-lg-8">
                        <label class="form-label">Select Election Card To Delete</label>
                        <select name="election_id" class="form-select form-select-lg">
                            <option value="">Choose election</option>
                            @foreach($elections as $election)
                                <option value="{{ $election->id }}">{{ $election->name }}{{ $election->place?->name ? ' - '.$election->place->name : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4 d-grid">
                        <button class="btn btn-outline-danger btn-lg">Delete Added Election Card</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        document.querySelectorAll('[data-qr-text]').forEach((container) => {
            if (! window.QRCode) {
                return;
            }

            new QRCode(container, {
                text: container.dataset.qrText,
                width: 132,
                height: 132,
                colorDark: '#172554',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        });

        document.querySelectorAll('[data-copy-link]').forEach((button) => {
            button.addEventListener('click', async () => {
                await navigator.clipboard.writeText(button.dataset.copyLink);
                const originalText = button.textContent;
                button.textContent = 'Copied';
                window.setTimeout(() => button.textContent = originalText, 1400);
            });
        });
    </script>
@endpush
