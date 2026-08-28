<?php

namespace App\Http\Controllers;

use App\Http\Requests\HostRegistrationRequest;
use App\Mail\RegistrationOtpMail;
use App\Models\HostRegistrationOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class HostRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.host-register');
    }

    public function store(HostRegistrationRequest $request): RedirectResponse
    {
        $imagePath = $request->file('image')->store('hosts', 'public');
        $email = $request->string('email')->lower()->toString();
        $otp = (string) random_int(100000, 999999);

        HostRegistrationOtp::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $request->string('name')->toString(),
                'contact_number' => $request->string('contact_number')->toString(),
                'password' => Hash::make($request->string('password')->toString()),
                'reason_type' => $request->string('reason_type')->toString(),
                'reason_message' => $request->string('reason_message')->toString(),
                'image_path' => $imagePath,
                'otp' => Hash::make($otp),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10),
            ]
        );

        Mail::to($email)->send(new RegistrationOtpMail(
            $request->string('name')->toString(),
            $otp,
            10,
        ));

        return redirect()
            ->route('hosts.otp', ['email' => $email])
            ->with('status', 'We sent a 6 digit verification code to your email. Enter it below to complete your host request.');
    }

    public function showOtp(Request $request): View
    {
        return view('auth.host-register-otp', [
            'email' => $request->query('email', ''),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.digits' => 'Please enter the 6 digit code sent to your email.',
        ]);

        $email = strtolower(trim($validated['email']));
        $pending = HostRegistrationOtp::query()->where('email', $email)->first();

        if (! $pending) {
            return back()
                ->withErrors(['otp' => 'No pending host request was found for this email. Please register again.'])
                ->withInput(['email' => $email]);
        }

        if ($pending->isExpired()) {
            $pending->delete();

            return redirect()
                ->route('hosts.create')
                ->withErrors(['email' => 'Your verification code expired. Please register again.']);
        }

        if ($pending->attempts >= 5) {
            $pending->delete();

            return redirect()
                ->route('hosts.create')
                ->withErrors(['email' => 'Too many invalid attempts. Please register again.']);
        }

        if (! Hash::check($validated['otp'], $pending->otp)) {
            $pending->increment('attempts');

            return back()
                ->withErrors(['otp' => 'Invalid verification code. Please try again.'])
                ->withInput(['email' => $email]);
        }

        DB::transaction(function () use ($pending) {
            User::purgeRejectedConflicts($pending->contact_number, $pending->email);

            $host = User::query()->create([
                'name' => $pending->name,
                'contact_number' => $pending->contact_number,
                'email' => $pending->email,
                'password' => $pending->password,
                'role' => 'host',
                'status' => 'pending',
                'image_path' => $pending->image_path,
            ]);

            $host->hostProfile()->create([
                'reason_type' => $pending->reason_type,
                'reason_message' => $pending->reason_message,
            ]);

            $pending->delete();
        });

        return redirect()
            ->route('login')
            ->with('status', 'Host account request submitted successfully. Please wait for admin approval.');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($validated['email']));
        $pending = HostRegistrationOtp::query()->where('email', $email)->first();

        if (! $pending) {
            return redirect()
                ->route('hosts.create')
                ->withErrors(['email' => 'No pending host request was found for this email. Please register again.']);
        }

        $otp = (string) random_int(100000, 999999);

        $pending->update([
            'otp' => Hash::make($otp),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($email)->send(new RegistrationOtpMail($pending->name, $otp, 10));

        return back()->with('status', 'A new verification code has been sent to your email.');
    }
}
