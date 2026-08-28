<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Mail\RegistrationOtpMail;
use App\Models\Election;
use App\Models\RegistrationOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $login = $credentials['login'];
        $loginField = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'contact_number';

        if (! Auth::attempt([$loginField => $login, 'password' => $credentials['password']], $request->boolean('remember'))) {
            return back()->withErrors([
                'login' => 'The provided credentials do not match our records.',
            ])->onlyInput('login');
        }

        $request->session()->regenerate();

        $user = $request->user();

        if (! $user->isAdmin() && ! $user->isApproved()) {
            Auth::logout();

            return redirect()->route('login')->with('status', $user->status === 'rejected'
                ? ($user->rejection_message ?: ($user->isHost() ? 'Your host request was rejected.' : 'Your registration was rejected. You can try once again.'))
                : ($user->isHost() ? 'Your host account is pending admin approval.' : 'Your account is pending approval. Please wait a little longer.'));
        }

        return redirect()->intended($user->isAdmin() || $user->isHost() ? route('dashboard') : route('vote.index'));
    }

    public function showRegister(): View
    {
        return view('auth.register', [
            'elections' => $this->electionGroups(),
            'preselectedElection' => null,
        ]);
    }

    public function showRegisterFromInvite(string $token): View
    {
        $election = Election::query()
            ->with('place')
            ->where('invite_token', $token)
            ->firstOrFail();

        return view('auth.register', [
            'elections' => $this->electionGroups(),
            'preselectedElection' => $election,
        ]);
    }

    public function register(RegisterUserRequest $request): RedirectResponse
    {
        $imagePath = $request->file('image')->store('users', 'public');
        $email = $request->string('email')->lower()->toString();
        $otp = (string) random_int(100000, 999999);

        RegistrationOtp::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $request->string('name')->toString(),
                'contact_number' => $request->string('contact_number')->toString(),
                'password' => Hash::make($request->string('password')->toString()),
                'date_of_birth' => $request->date('date_of_birth'),
                'election_id' => (int) $request->election_id,
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
            ->route('register.otp', ['email' => $email])
            ->with('status', 'We sent a 6 digit verification code to your email. Enter it below to complete registration.');
    }

    public function showRegisterOtp(Request $request): View
    {
        return view('auth.register-otp', [
            'email' => $request->query('email', ''),
        ]);
    }

    public function verifyRegisterOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.digits' => 'Please enter the 6 digit code sent to your email.',
        ]);

        $email = strtolower(trim($validated['email']));
        $pending = RegistrationOtp::query()->where('email', $email)->first();

        if (! $pending) {
            return back()
                ->withErrors(['otp' => 'No pending registration was found for this email. Please register again.'])
                ->withInput(['email' => $email]);
        }

        if ($pending->isExpired()) {
            $pending->delete();

            return redirect()
                ->route('register')
                ->withErrors(['email' => 'Your verification code expired. Please register again.']);
        }

        if ($pending->attempts >= 5) {
            $pending->delete();

            return redirect()
                ->route('register')
                ->withErrors(['email' => 'Too many invalid attempts. Please register again.']);
        }

        if (! Hash::check($validated['otp'], $pending->otp)) {
            $pending->increment('attempts');

            return back()
                ->withErrors(['otp' => 'Invalid verification code. Please try again.'])
                ->withInput(['email' => $email]);
        }

        User::purgeRejectedConflicts($pending->contact_number, $pending->email);

        $user = User::create([
            'name' => $pending->name,
            'contact_number' => $pending->contact_number,
            'email' => $pending->email,
            'password' => $pending->password,
            'role' => 'user',
            'status' => 'pending',
            'date_of_birth' => $pending->date_of_birth,
            'election_id' => $pending->election_id,
            'last_known_election_name' => $pending->election?->name,
            'image_path' => $pending->image_path,
        ]);

        $pending->delete();

        return redirect()
            ->route('login')
            ->with('status', 'Registration submitted successfully. Please wait for admin approval.');
    }

    public function resendRegisterOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($validated['email']));
        $pending = RegistrationOtp::query()->where('email', $email)->first();

        if (! $pending) {
            return redirect()
                ->route('register')
                ->withErrors(['email' => 'No pending registration was found for this email. Please register again.']);
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

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function electionGroups()
    {
        return Election::query()
            ->with('place')
            ->orderBy('name')
            ->get()
            ->groupBy('name')
            ->map(fn ($cards, string $name) => [
                'name' => $name,
                'places' => $cards
                    ->map(fn (Election $election) => [
                        'id' => $election->id,
                        'name' => $election->place?->name ?? $election->name,
                    ])
                    ->values(),
            ])
            ->values();
    }
}
