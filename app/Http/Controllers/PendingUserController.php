<?php

namespace App\Http\Controllers;

use App\Http\Requests\PendingUserActionRequest;
use App\Mail\AccountRejectedMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class PendingUserController extends Controller
{
    public function update(PendingUserActionRequest $request, User $user): RedirectResponse
    {
        $this->authorizeUserAccess($user);

        if ($request->string('action')->toString() === 'accept') {
            $user->update([
                'status' => 'approved',
                'approved_at' => now(),
                'rejection_message' => null,
            ]);

            return back()->with('status', 'User accepted successfully.');
        }

        $rejectionMessage = $request->input('rejection_message') ?: 'You can try once again';

        $user->update([
            'status' => 'rejected',
            'rejection_message' => $rejectionMessage,
        ]);

        if ($user->email) {
            Mail::to($user->email)->send(new AccountRejectedMail($user->name, $rejectionMessage));
        }

        return back()->with('status', 'User rejected successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeUserAccess($user);

        $user->delete();

        return back()->with('status', 'Pending user deleted successfully.');
    }

    private function authorizeUserAccess(User $user): void
    {
        if (auth()->user()->isHost() && (int) $user->election?->host_id !== (int) auth()->id()) {
            abort(403);
        }
    }
}
