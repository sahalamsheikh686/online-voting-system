@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-7">
            <div class="panel-card p-4 p-lg-5">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <span class="eyebrow">Email Verification</span>
                        <h1 class="h2 mt-2 mb-1">Enter verification code</h1>
                        <p class="text-secondary mb-0">We sent a 6 digit code to {{ $email ?: 'your email' }}. It expires in 10 minutes.</p>
                    </div>
                    <a href="{{ route('hosts.create') }}" class="btn btn-outline-secondary rounded-pill px-4">Back to Register</a>
                </div>

                <form action="{{ route('hosts.otp.store') }}" method="POST" class="row g-4" autocomplete="off">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control form-control-lg" placeholder="host@example.com" autocomplete="off">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Verification Code</label>
                        <input type="text" name="otp" value="" class="form-control form-control-lg" placeholder="6 digit code" maxlength="6" inputmode="numeric" autocomplete="one-time-code">
                    </div>
                    <div class="col-12 d-flex gap-3">
                        <button class="btn btn-primary px-4">Verify &amp; Submit Host Request</button>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>

                <form action="{{ route('hosts.otp.resend') }}" method="POST" class="mt-3">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button class="btn btn-link px-0">Didn't get the code? Resend</button>
                </form>
            </div>
        </div>
    </div>
@endsection
