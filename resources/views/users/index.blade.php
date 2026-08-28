@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <span class="eyebrow">User Management</span>
            <h1 class="h2 mt-2 mb-1">Voter management</h1>
            <p class="text-secondary mb-0">Review pending registrations, then manage approved voters by election.</p>
        </div>
    </div>

    <div class="panel-card p-4 mb-4">
        <span class="eyebrow">Pending Registrations</span>
        <h2 class="h4 mt-2 mb-3">Pending registrations</h2>
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

    <div class="panel-card p-4 mb-4">
        <span class="eyebrow">Approved Voters</span>
        <h2 class="h4 mt-2 mb-3">Approved users</h2>
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Election Filter</label>
                <select name="election_id" class="form-select">
                    <option value="">All elections</option>
                    @foreach($elections as $election)
                        <option value="{{ $election->id }}" @selected($selectedElection == $election->id)>{{ $election->name }}{{ $election->place?->name ? ' - '.$election->place->name : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-primary w-100">Apply Filter</button>
            </div>
            <div class="col-md-3">
                <a href="{{ route('users.index', ['show_all' => 1]) }}" class="btn btn-primary w-100">Show All Users</a>
            </div>
        </form>
    </div>

    <div class="panel-card p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Current Image</th>
                        <th>Contact</th>
                        <th>Election</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>
                                @if($user->image_path)
                                    <img src="{{ asset('storage/'.$user->image_path) }}" alt="{{ $user->name }}" class="table-avatar">
                                @else
                                    <div class="table-avatar table-avatar-placeholder">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                @endif
                            </td>
                            <td>{{ $user->contact_number }}</td>
                            <td>{{ $user->election?->name }}{{ $user->election?->place?->name ? ' - '.$user->election->place->name : '' }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-dark" data-bs-toggle="collapse" data-bs-target="#editUser{{ $user->id }}">Edit</button>
                            </td>
                        </tr>
                        <tr class="collapse" id="editUser{{ $user->id }}">
                            <td colspan="5">
                                <div class="row g-3">
                                    <div class="col-lg-10">
                                        <form action="{{ route('users.update', $user) }}" method="POST" class="row g-3">
                                            @csrf
                                            @method('PUT')
                                            <div class="col-md-4">
                                                <input type="text" name="name" value="{{ $user->name }}" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" name="contact_number" value="{{ $user->contact_number }}" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <select name="election_id" class="form-select">
                                                    @foreach($elections as $election)
                                                        <option value="{{ $election->id }}" @selected($user->election_id == $election->id)>{{ $election->name }}{{ $election->place?->name ? ' - '.$election->place->name : '' }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 d-flex gap-2">
                                                <button class="btn btn-primary btn-sm px-4">Save</button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-toggle="collapse" data-bs-target="#editUser{{ $user->id }}">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-lg-2">
                                        <form action="{{ route('users.destroy', $user) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Delete this user?')">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">No approved users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </div>
@endsection
