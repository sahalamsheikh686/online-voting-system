@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-7">
            <div class="panel-card p-4 p-lg-5">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <span class="eyebrow">Password Reset</span>
                        <h1 class="h2 mt-2 mb-1">Find your account</h1>
                        <p class="text-secondary mb-0">Enter your registered voter contact number or host email.</p>
                    </div>
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary rounded-pill px-4">Back to Login</a>
                </div>

                <form action="{{ route('password.email') }}" method="POST" class="row g-4" autocomplete="off">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">Contact Number / Email</label>
                        <input
                            type="text"
                            name="login"
                            value="{{ old('login') }}"
                            class="form-control form-control-lg"
                            placeholder="98xxxxxxxx or host@example.com"
                            title="Enter your registered 10 digit contact number or email."
                            autocomplete="off"
                        >
                    </div>
                    <div class="col-12">
                        <div class="guidance-note">
                            Voter accounts can reset by contact number. Host accounts can reset by email or contact number.
                        </div>
                    </div>
                    <div class="col-12 d-flex gap-3">
                        <button class="btn btn-primary px-4">Continue</button>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
