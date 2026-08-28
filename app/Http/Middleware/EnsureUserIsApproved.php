<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isAdmin() || $user->isApproved()) {
            if ($user->isHost()) {
                return redirect()->route('dashboard');
            }

            return $next($request);
        }

        Auth::logout();

        return redirect()
            ->route('login')
            ->with('status', $user->status === 'rejected'
                ? ($user->rejection_message ?: 'Your registration was rejected. You can try once again.')
                : 'Your registration is still pending admin approval.');
    }
}
