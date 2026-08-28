@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <span class="eyebrow">New User Management</span>
            <h1 class="h2 mt-2 mb-1">Pending registrations</h1>
            <p class="text-secondary mb-0">Accept users so they can log in, or reject them with a correction note.</p>
        </div>
    </div>

    @if($elections->isNotEmpty())
        <div class="panel-card p-4 mb-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Election Place Filter</label>
                    <select name="election_id" class="form-select">
                        <option value="">All election places</option>
                        @foreach($elections as $election)
                            <option value="{{ $election->id }}" @selected($selectedElection == $election->id)>{{ $election->name }}{{ $election->place?->name ? ' - '.$election->place->name : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100">Apply</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('pending-users.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    @endif

    <div class="panel-card p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Current Image</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Election</th>
                        <th>Message / Action</th>
                        <th class="text-end">Delete</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingUsers as $user)
                        <tr>
                            <td>
                                @if($user->image_path)
                                    <img src="{{ asset('storage/'.$user->image_path) }}" alt="{{ $user->name }}" class="table-avatar">
                                @else
                                    <div class="table-avatar table-avatar-placeholder">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                @endif
                            </td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->contact_number }}</td>
                            <td>{{ $user->election?->name }}{{ $user->election?->place?->name ? ' - '.$user->election->place->name : '' }}</td>
                            <td>
                                <form action="{{ route('pending-users.update', $user) }}" method="POST" class="row g-2">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-12">
                                        <textarea name="rejection_message" rows="2" class="form-control" placeholder="You can try once again or add a custom correction note"></textarea>
                                    </div>
                                    <div class="col-12 d-flex gap-2">
                                        <button name="action" value="accept" class="btn btn-sm btn-success">Accept</button>
                                        <button name="action" value="reject" class="btn btn-sm btn-warning">Reject</button>
                                    </div>
                                </form>
                            </td>
                            <td class="text-end">
                                <form action="{{ route('pending-users.destroy', $user) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this pending user?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">No pending user registrations right now.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
