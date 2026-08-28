@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="panel-card p-4 p-lg-5">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <span class="eyebrow">Candidate Management</span>
                        <h1 class="h2 mt-2 mb-1">{{ $pageTitle }}</h1>
                    </div>
                    <a href="{{ route('candidates.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                </div>

                <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="row g-4">
                    @csrf
                    @if($formMethod !== 'POST')
                        @method($formMethod)
                    @endif

                    <div class="col-md-6">
                        <label class="form-label">Candidate Name</label>
                        <input type="text" name="name" value="{{ old('name', $candidate->name) }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Candidate Age</label>
                        <input type="number" name="age" value="{{ old('age', $candidate->age) }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Candidate Position</label>
                        <select name="position" class="form-select">
                            @foreach(['President', 'Vice President'] as $position)
                                <option value="{{ $position }}" @selected(old('position', $candidate->position ?: 'President') === $position)>{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Candidate Area (Election)</label>
                        @php($selectedElectionName = old('election_name', collect($elections)->first(fn ($election) => collect($election['places'])->contains('id', (int) old('election_id', $candidate->election_id)))['name'] ?? ''))
                        <select name="election_name" class="form-select" id="candidateElection">
                            <option value="">Select election</option>
                            @foreach($elections as $election)
                                <option value="{{ $election['name'] }}" @selected($selectedElectionName === $election['name'])>{{ $election['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Candidate Area (Place)</label>
                        <select name="election_id" class="form-select" id="candidatePlace">
                            <option value="">Select place</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Candidate Email</label>
                        <input type="email" name="email" value="{{ old('email', $candidate->email) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Candidate Image</label>
                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Candidate Vision</label>
                        <input type="file" name="vision" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                    </div>
                    <div class="col-12 d-flex gap-3">
                        <button class="btn btn-primary px-4">Add</button>
                        <a href="{{ route('candidates.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const candidateElectionGroups = @json($elections);
        const candidateElection = document.getElementById('candidateElection');
        const candidatePlace = document.getElementById('candidatePlace');
        let selectedCandidatePlaceId = @json((string) old('election_id', $candidate->election_id));

        const syncCandidatePlaces = () => {
            if (!candidateElection || !candidatePlace) {
                return;
            }

            const group = candidateElectionGroups.find((election) => election.name === candidateElection.value);
            candidatePlace.innerHTML = '<option value="">Select place</option>';

            if (!group) {
                return;
            }

            group.places.forEach((place) => {
                const option = document.createElement('option');
                option.value = place.id;
                option.textContent = place.name;
                option.selected = String(place.id) === selectedCandidatePlaceId;
                candidatePlace.appendChild(option);
            });

            if (group.places.length === 1 && !selectedCandidatePlaceId) {
                candidatePlace.value = group.places[0].id;
            }
        };

        candidateElection?.addEventListener('change', () => {
            selectedCandidatePlaceId = '';
            syncCandidatePlaces();
        });
        syncCandidatePlaces();
    </script>
@endpush
