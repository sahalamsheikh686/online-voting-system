@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <span class="eyebrow">Candidate Management</span>
            <h1 class="h2 mt-2 mb-1">Election candidates</h1>
            <p class="text-secondary mb-0">Filter by election, add new candidates, and manage existing records.</p>
        </div>
        <a href="{{ route('candidates.create') }}" class="btn btn-primary rounded-pill px-4">Add Candidate</a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Filter by Election</label>
                        <select name="election_id" class="form-select">
                            <option value="">All elections</option>
                            @foreach($elections as $election)
                                <option value="{{ $election->id }}" @selected($selectedElection == $election->id)>{{ $election->name }}{{ $election->place?->name ? ' - '.$election->place->name : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button class="btn btn-outline-primary w-100">Apply</button>
                        <a href="{{ route('candidates.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="panel-card p-4">
                <h2 class="h5 mb-3">Add Election</h2>
                <form action="{{ route('elections.store') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-12">
                        <input type="text" name="name" class="form-control" placeholder="Election name">
                    </div>
                    <div class="col-12">
                        <input type="text" name="election_title" class="form-control" placeholder="Election title">
                    </div>
                    <div class="col-12">
                        <input type="text" name="place_name" class="form-control" placeholder="Election place" title="Election place must contain text. Numbers only are not valid.">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary flex-grow-1">Add Election</button>
                        <button type="reset" class="btn btn-outline-secondary flex-grow-1">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="panel-card p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Election</th>
                        <th>Email</th>
                        <th>Vision</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($candidates as $candidate)
                        <tr>
                            <td>
                                @if($candidate->image_path)
                                    <img src="{{ asset('storage/'.$candidate->image_path) }}" alt="{{ $candidate->name }}" class="table-avatar">
                                @else
                                    <div class="table-avatar table-avatar-placeholder">{{ strtoupper(substr($candidate->name, 0, 1)) }}</div>
                                @endif
                            </td>
                            <td>{{ $candidate->name }}</td>
                            <td>{{ $candidate->age }}</td>
                            <td>{{ $candidate->election?->name }}{{ $candidate->election?->place?->name ? ' - '.$candidate->election->place->name : '' }}</td>
                            <td>{{ $candidate->email }}</td>
                            <td>
                                @if($candidate->vision_path)
                                    <a href="{{ asset('storage/'.$candidate->vision_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View Vision</a>
                                @else
                                    <span class="text-secondary small">No file</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('candidates.edit', $candidate) }}" class="btn btn-sm btn-outline-dark">Edit</a>
                                    <form action="{{ route('candidates.destroy', $candidate) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this candidate?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">No candidates found for this election filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $candidates->links() }}
    </div>
@endsection
