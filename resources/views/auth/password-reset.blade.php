@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-7">
            <div class="panel-card p-4 p-lg-5">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <span class="eyebrow">Password Reset</span>
                        <h1 class="h2 mt-2 mb-1">Create new password</h1>
                        <p class="text-secondary mb-0">Use at least 8 characters and confirm both passwords.</p>
                    </div>
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary rounded-pill px-4">Back to Login</a>
                </div>

                <form action="{{ route('password.update') }}" method="POST" class="row g-4" id="passwordResetForm" autocomplete="off">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="col-12">
                        <label class="form-label">Contact Number / Email</label>
                        <input
                            type="text"
                            name="login"
                            value="{{ old('login', $login) }}"
                            class="form-control form-control-lg"
                            placeholder="98xxxxxxxx or host@example.com"
                            autocomplete="off"
                        >
                    </div>
                    <div class="col-12">
                        <label class="form-label">New Password</label>
                        <div class="input-group password-input-group">
                            <input type="password" name="password" class="form-control form-control-lg" id="reset_password" minlength="8" autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-password-toggle data-password-target="reset_password" aria-label="Show password" aria-pressed="false">
                                <span data-password-icon aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group password-input-group">
                            <input type="password" name="password_confirmation" class="form-control form-control-lg" id="reset_password_confirmation" minlength="8" autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-password-toggle data-password-target="reset_password_confirmation" aria-label="Show confirm password" aria-pressed="false">
                                <span data-password-icon aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 d-flex gap-3">
                        <button class="btn btn-primary px-4">Reset Password</button>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const resetPasswordForm = document.getElementById('passwordResetForm');
        const resetPassword = document.getElementById('reset_password');
        const resetPasswordConfirmation = document.getElementById('reset_password_confirmation');

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

        if (resetPasswordForm && resetPassword && resetPasswordConfirmation) {
            const syncPasswordValidation = () => {
                if (resetPasswordConfirmation.value && resetPassword.value !== resetPasswordConfirmation.value) {
                    resetPasswordConfirmation.setCustomValidity('Confirm your both password');
                } else {
                    resetPasswordConfirmation.setCustomValidity('');
                }
            };

            resetPassword.addEventListener('input', syncPasswordValidation);
            resetPasswordConfirmation.addEventListener('input', syncPasswordValidation);
            resetPasswordForm.addEventListener('submit', syncPasswordValidation);
        }
    </script>
@endpush
