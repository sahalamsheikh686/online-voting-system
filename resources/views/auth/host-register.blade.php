@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="panel-card p-4 p-lg-5">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <span class="eyebrow">Host Registration</span>
                        <h1 class="h2 mt-2 mb-1">Create host account</h1>
                        <p class="text-secondary mb-0">Submit your hosting request. An admin will review it before dashboard access is opened.</p>
                    </div>
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                </div>

                <form action="{{ route('hosts.store') }}" method="POST" enctype="multipart/form-data" class="row g-4" autocomplete="off">
                    @csrf
                    <input type="text" name="host_fake_username" class="d-none" tabindex="-1" autocomplete="username">
                    <input type="password" name="host_fake_password" class="d-none" tabindex="-1" autocomplete="new-password">

                    <div class="col-md-6">
                        <label class="form-label">Host Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Only alphabet characters" title="Host name must contain only alphabets and spaces." autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Host Contact</label>
                        <input type="text" name="contact_number" value="{{ old('contact_number') }}" class="form-control" placeholder="Exactly 10 digits" maxlength="10" inputmode="numeric" title="Host contact must be exactly 10 digits.">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reason For Host</label>
                        <select name="reason_type" class="form-select">
                            <option value="">Select reason</option>
                            @foreach(['School', 'College', 'Random'] as $reason)
                                <option value="{{ $reason }}" @selected(old('reason_type') === $reason)>{{ $reason }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="host@example.com" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <div class="input-group password-input-group">
                            <input type="password" name="password" class="form-control" id="host_password" minlength="8" autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-password-toggle data-password-target="host_password" aria-label="Show password">Show</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group password-input-group">
                            <input type="password" name="password_confirmation" class="form-control" id="host_password_confirmation" minlength="8" autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-password-toggle data-password-target="host_password_confirmation" aria-label="Show confirm password">Show</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Profile Picture</label>
                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Why you want to Host Election</label>
                        <textarea name="reason_message" rows="5" class="form-control" placeholder="Write 10 to 150 words. Text with numbers is valid, numbers only is not valid.">{{ old('reason_message') }}</textarea>
                        <div class="form-text">Minimum 10 words, maximum 150 words.</div>
                    </div>
                    <div class="col-12">
                        <div class="guidance-note">
                            Validation:
                            Host Name = alphabets and spaces only.
                            Contact = exactly 10 digits.
                            Email = valid and unique.
                            Message = 10 to 150 words, numbers only not valid.
                            Profile Picture = jpg, jpeg, or png only.
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
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.dataset.passwordTarget);

                if (! target) {
                    return;
                }

                const isHidden = target.type === 'password';
                target.type = isHidden ? 'text' : 'password';
                button.textContent = isHidden ? 'Hide' : 'Show';
            });
        });
    </script>
@endpush
