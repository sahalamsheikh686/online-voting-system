<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function create(): View
    {
        return view('auth.password-request');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
        ], [
            'login.required' => 'Please enter your registered contact number or email.',
        ]);

        $login = trim($validated['login']);
        $user = $this->findUser($login);

        if (! $user) {
            return back()
                ->withErrors(['login' => 'No account was found for this contact number or email.'])
                ->onlyInput('login');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $this->resetKey($login)],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        return redirect()
            ->route('password.reset', ['token' => $token, 'login' => $login])
            ->with('status', 'Password reset link is ready. Please set your new password.');
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.password-reset', [
            'token' => $token,
            'login' => $request->query('login', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'login.required' => 'Please enter your registered contact number or email.',
            'password.confirmed' => 'Confirm your both password.',
        ]);

        $login = trim($validated['login']);
        $record = DB::table('password_reset_tokens')->where('email', $this->resetKey($login))->first();

        if (
            ! $record ||
            ! Hash::check($validated['token'], $record->token) ||
            Carbon::parse($record->created_at)->lt(now()->subMinutes(60))
        ) {
            return back()
                ->withErrors(['login' => 'This password reset link is invalid or expired.'])
                ->withInput(['login' => $login]);
        }

        $user = $this->findUser($login);

        if (! $user) {
            return back()
                ->withErrors(['login' => 'No account was found for this contact number or email.'])
                ->withInput(['login' => $login]);
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('password_reset_tokens')->where('email', $this->resetKey($login))->delete();

        return redirect()
            ->route('login')
            ->with('status', 'Password changed successfully. You can login with your new password.');
    }

    private function findUser(string $login): ?User
    {
        return User::query()
            ->where(filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'contact_number', $login)
            ->first();
    }

    private function resetKey(string $login): string
    {
        return strtolower(trim($login));
    }
}
