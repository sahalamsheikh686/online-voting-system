@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <span class="eyebrow">Host Management</span>
            <h1 class="h2 mt-2 mb-1">Host account management</h1>
            <p class="text-secondary mb-0">Review submitted host accounts, then accept or reject with feedback.</p>
        </div>
    </div>

    <div class="panel-card p-4 mb-4">
        <span class="eyebrow">Pending Requests</span>
        <h2 class="h4 mt-2 mb-3">Host account requests</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Profile</th>
                        <th>Host</th>
                        <th>Reason</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Action</th>
                        <th class="text-end">Delete</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRequests as $host)
                        <tr>
                            <td>
                                @if($host->image_path)
                                    <img src="{{ asset('storage/'.$host->image_path) }}" alt="{{ $host->name }}" class="table-avatar">
                                @else
                                    <div class="table-avatar table-avatar-placeholder">{{ strtoupper(substr($host->name, 0, 1)) }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $host->name }}</div>
                                <div class="small text-secondary">{{ $host->contact_number }}</div>
                                <div class="small text-secondary">{{ $host->email }}</div>
                            </td>
                            <td>{{ $host->hostProfile?->reason_type }}</td>
                            <td class="small text-secondary" style="min-width: 260px;">{{ $host->hostProfile?->reason_message }}</td>
                            <td>{{ ucfirst($host->status) }}</td>
                            <td>
                                <form action="{{ route('host-requests.update', $host) }}" method="POST" class="row g-2">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-12">
                                        <textarea name="rejection_message" rows="2" class="form-control" placeholder="Feedback message for rejected host">{{ $host->rejection_message }}</textarea>
                                    </div>
                                    <div class="col-12 d-flex gap-2">
                                        <button name="action" value="accept" class="btn btn-sm btn-success">Accept</button>
                                        <button name="action" value="reject" class="btn btn-sm btn-warning">Reject</button>
                                    </div>
                                </form>
                            </td>
                            <td class="text-end">
                                <form action="{{ route('host-requests.destroy', $host) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this host request?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">No pending or rejected host requests.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <span class="eyebrow">Approved Hosts</span>
                <h2 class="h4 mt-2 mb-0">All approved hosts</h2>
            </div>
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="name" value="{{ $nameSearch }}" class="form-control" placeholder="Search by name">
                <button class="btn btn-outline-primary">Search</button>
                @if($nameSearch)
                    <a href="{{ route('host-requests.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Profile</th>
                        <th>Host</th>
                        <th>Reason</th>
                        <th>Approved At</th>
                        <th class="text-end">Delete</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvedHosts as $host)
                        <tr>
                            <td>
                                @if($host->image_path)
                                    <img src="{{ asset('storage/'.$host->image_path) }}" alt="{{ $host->name }}" class="table-avatar">
                                @else
                                    <div class="table-avatar table-avatar-placeholder">{{ strtoupper(substr($host->name, 0, 1)) }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $host->name }}</div>
                                <div class="small text-secondary">{{ $host->contact_number }}</div>
                                <div class="small text-secondary">{{ $host->email }}</div>
                            </td>
                            <td>{{ $host->hostProfile?->reason_type }}</td>
                            <td>{{ $host->approved_at?->format('M d, Y h:i A') }}</td>
                            <td class="text-end">
                                <form action="{{ route('host-requests.destroy', $host) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this host account?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">
                                {{ $nameSearch ? 'No approved hosts match this search.' : 'No approved hosts yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
