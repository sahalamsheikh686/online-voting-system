@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="panel-card p-4 p-lg-5">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <span class="eyebrow">User Registration</span>
                        <h1 class="h2 mt-2 mb-1">Create your voter profile</h1>
                        <p class="text-secondary mb-0">Your registration will stay pending until an admin reviews and accepts it.</p>
                    </div>
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary rounded-pill px-4">Back to Login</a>
                </div>

                <form action="{{ route('register.store') }}" method="POST" enctype="multipart/form-data" class="row g-4" id="registerForm" autocomplete="off">
                    @csrf
                    <input type="text" name="register_fake_username" class="d-none" tabindex="-1" autocomplete="username">
                    <input type="password" name="register_fake_password" class="d-none" tabindex="-1" autocomplete="new-password">
                    <div class="col-md-6">
                        <label class="form-label">User Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Only alphabet characters" title="User name must contain only alphabets and spaces." autocomplete="off" autocapitalize="words" autocorrect="off" spellcheck="false" readonly onfocus="this.removeAttribute('readonly');">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">User Contact</label>
                        <input type="text" name="contact_number" value="{{ old('contact_number') }}" class="form-control" placeholder="Exactly 10 digits" maxlength="10" inputmode="numeric" title="Contact number must be exactly 10 digits." autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false" readonly onfocus="this.removeAttribute('readonly');">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="you@example.com" title="Enter a valid email address. A verification code will be sent here." autocomplete="off">
                        <div class="form-text">We will send a 6 digit verification code to this email.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <div class="input-group password-input-group">
                            <input type="password" name="password" class="form-control" id="password" minlength="8" title="Password must be at least 8 characters." autocomplete="new-password" autocapitalize="off" autocorrect="off" spellcheck="false" readonly onfocus="this.removeAttribute('readonly');">
                            <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-password-toggle data-password-target="password" aria-label="Show password" aria-pressed="false">
                                <span data-password-icon aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group password-input-group">
                            <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" minlength="8" title="Confirm password must match password." autocomplete="new-password" autocapitalize="off" autocorrect="off" spellcheck="false" readonly onfocus="this.removeAttribute('readonly');">
                            <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-password-toggle data-password-target="password_confirmation" aria-label="Show confirm password" aria-pressed="false">
                                <span data-password-icon aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">User Age / Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="form-control" max="{{ now()->subYears(18)->toDateString() }}">
                        <div class="form-text">Only users aged 18 or above can register.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Election</label>
                        @if($preselectedElection)
                            <input type="hidden" name="election_name" value="{{ $preselectedElection->name }}">
                            <input type="text" class="form-control" value="{{ $preselectedElection->name }}" readonly>
                            <div class="form-text">This invite link is locked to this election.</div>
                        @else
                            @php($selectedRegisterElection = collect($elections)->first(fn ($election) => collect($election['places'])->contains('id', (int) old('election_id'))))
                            @php($selectedRegisterElectionName = old('election_name', $selectedRegisterElection['name'] ?? ''))
                            <select name="election_name" class="form-select" id="registerElection">
                                <option value="">Select election</option>
                                @foreach($elections as $election)
                                    <option value="{{ $election['name'] }}" @selected($selectedRegisterElectionName === $election['name'])>{{ $election['name'] }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Election Place</label>
                        @if($preselectedElection)
                            <input type="hidden" name="election_id" value="{{ $preselectedElection->id }}">
                            <input type="text" class="form-control" value="{{ $preselectedElection->place?->name ?? $preselectedElection->name }}" readonly>
                            <div class="form-text">Your vote panel will open for this exact election place.</div>
                        @else
                            <select name="election_id" class="form-select" id="registerPlace">
                                <option value="">Select election place</option>
                            </select>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Current Image</label>
                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png">
                    </div>
                    <div class="col-12">
                        <div class="guidance-note">
                            Validation:
                            Name = alphabets and spaces only.
                            Contact = exactly 10 digits only.
                            Email = valid and unique, used to send your verification code.
                            Password = at least 8 characters and both passwords must match.
                            Date of Birth = age must be 18 or above.
                            Current Image = jpg, jpeg, or png only.
                        </div>
                    </div>
                    <div class="col-12 d-flex gap-3">
                        <button class="btn btn-primary px-4">Submit</button>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const registerForm = document.getElementById('registerForm');
        const password = document.getElementById('password');
        const passwordConfirmation = document.getElementById('password_confirmation');
        const registerElectionGroups = @json($elections);
        const registerElection = document.getElementById('registerElection');
        const registerPlace = document.getElementById('registerPlace');
        let selectedRegisterPlaceId = @json((string) old('election_id'));

        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.dataset.passwordTarget);

                if (! target) {
                    return;
                }

                const isHidden = target.type === 'password';
                target.type = isHidden ? 'text' : 'password';
                button.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            });
        });

        if (registerForm && password && passwordConfirmation) {
            const syncPasswordValidation = () => {
                if (passwordConfirmation.value && password.value !== passwordConfirmation.value) {
                    passwordConfirmation.setCustomValidity('Confirm your both password');
                } else {
                    passwordConfirmation.setCustomValidity('');
                }
            };

            password.addEventListener('input', syncPasswordValidation);
            passwordConfirmation.addEventListener('input', syncPasswordValidation);
            registerForm.addEventListener('submit', syncPasswordValidation);
        }

        const syncRegisterPlaces = () => {
            if (! registerElection || ! registerPlace) {
                return;
            }

            const group = registerElectionGroups.find((election) => election.name === registerElection.value);
            registerPlace.innerHTML = '<option value="">Select election place</option>';

            if (! group) {
                return;
            }

            group.places.forEach((place) => {
                const option = document.createElement('option');
                option.value = place.id;
                option.textContent = place.name;
                option.selected = String(place.id) === selectedRegisterPlaceId;
                registerPlace.appendChild(option);
            });

            if (group.places.length === 1 && ! selectedRegisterPlaceId) {
                registerPlace.value = group.places[0].id;
            }
        };

        registerElection?.addEventListener('change', () => {
            selectedRegisterPlaceId = '';
            syncRegisterPlaces();
        });
        syncRegisterPlaces();
    </script>
@endpush
