@extends('layouts.app')

@section('content')
    <div class="row justify-content-center auth-page-row">
        <div class="col-xl-11 col-lg-11">
            <div class="auth-split-card">
                <div class="auth-split-left">
                    <span class="eyebrow auth-split-eyebrow">Secure Online Voting</span>
                    <h1 class="auth-split-title">
                        <span>Your vote,</span><br>
                        <span>your right,</span><br>
                        <span>your election,</span><br>
                        <span>your voice.</span>
                    </h1>
                    <p class="auth-split-description">A secure digital ballot space for fair elections, transparent participation, and trusted voting access.</p>

                    <div class="auth-feature-stack">
                        <div class="auth-feature-card">
                            <div class="auth-feature-heading">One Person, One Vote</div>
                            <p class="mb-0">Each voter gets one protected chance to select election leaders with confidence.</p>
                        </div>
                        <div class="auth-feature-card">
                            <div class="auth-feature-heading">Safe And Transparent</div>
                            <p class="mb-0">Password login, election-based access,Email Verification, and live election flow keep the process secure.</p>
                        </div>
                    </div>

                    <div class="auth-info-grid mt-4">
                        <div class="auth-info-card">
                            <div class="auth-info-value">3 Roles</div>
                            <div class="auth-info-label">Admin, Host, and Voter access with separate permissions.</div>
                        </div>
                        <div class="auth-info-card">
                            <div class="auth-info-value">Election Wise</div>
                            <div class="auth-info-label">Candidates and ballots stay mapped to the correct election.</div>
                        </div>
                        <div class="auth-info-card">
                            <div class="auth-info-value">Password Login</div>
                            <div class="auth-info-label">Contact number and password open the correct portal.</div>
                        </div>
                    </div>
                </div>

                <div class="auth-split-right">
                    <div class="auth-login-card">
                        <div class="auth-login-badge">OVS</div>
                        <div class="auth-login-header mb-3 text-center">
                            <h2 class="h2 mb-2">Hello Again!</h2>
                            <p class="text-secondary mb-0">Sign in with your registered contact number or host email and password.</p>
                        </div>

                        <form action="{{ route('login.store') }}" method="POST" class="row g-3" autocomplete="off">
                            @csrf
                            <input type="text" name="login_fake_username" class="d-none" tabindex="-1" autocomplete="username">
                            <input type="password" name="login_fake_password" class="d-none" tabindex="-1" autocomplete="new-password">
                            <div class="col-12">
                                <label class="form-label">Contact Number / Email</label>
                                <input type="text" name="login" value="{{ old('login', old('contact_number')) }}" class="form-control form-control-lg" placeholder="98xxxxxxxx or host@example.com" title="Enter a 10 digit contact number or valid host email." autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false" readonly onfocus="this.removeAttribute('readonly');">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Password</label>
                                <div class="input-group password-input-group">
                                    <input type="password" name="password" id="login_password" class="form-control form-control-lg" placeholder="Enter your password" minlength="8" title="Password must be at least 8 characters." autocomplete="new-password" autocapitalize="off" autocorrect="off" spellcheck="false" readonly onfocus="this.removeAttribute('readonly');">
                                    <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-password-toggle data-password-target="login_password" aria-label="Show password" aria-pressed="false">
                                        <span data-password-icon aria-hidden="true">
                                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 form-check ms-1">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <div class="col-12 text-end">
                                <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot password?</a>
                            </div>
                            <div class="col-12 d-grid">
                                <button class="btn btn-primary btn-lg">Login</button>
                            </div>
                        </form>

                        <div class="mt-3 text-center">
                            <a href="{{ route('register') }}" class="text-decoration-none">Need a voter account? Register here.</a>
                        </div>
                        <div class="mt-2 text-center">
                            <a href="{{ route('hosts.create') }}" class="text-decoration-none">Create Host Account</a>
                        </div>
                    </div>
                </div>
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
                button.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            });
        });
    </script>
@endpush
