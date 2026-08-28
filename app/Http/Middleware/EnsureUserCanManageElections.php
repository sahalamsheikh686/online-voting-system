<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManageElections
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isAdmin() || ($user->isHost() && $user->isApproved())) {
            return $next($request);
        }

        Auth::logout();

        return redirect()
            ->route('login')
            ->with('status', $user->status === 'rejected'
                ? ($user->rejection_message ?: 'Your host request was rejected.')
                : 'Your host account is still pending admin approval.');
    }
}
