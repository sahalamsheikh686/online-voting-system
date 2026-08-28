<?php

namespace App\Http\Controllers;

use App\Mail\AccountRejectedMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class HostRequestController extends Controller
{
    public function index(): View
    {
        return view('host-requests.index', [
            'hosts' => User::query()
                ->with('hostProfile')
                ->where('role', 'host')
                ->orderByRaw("case status when 'pending' then 0 when 'rejected' then 1 when 'approved' then 2 else 3 end")
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isHost(), 404);

        $validated = $request->validate([
            'action' => ['required', 'in:accept,reject'],
            'rejection_message' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['action'] === 'accept') {
            $user->update([
                'status' => 'approved',
                'approved_at' => now(),
                'rejection_message' => null,
            ]);

            return back()->with('status', 'Host request accepted successfully.');
        }

        $rejectionMessage = $validated['rejection_message'] ?: 'Your host request was rejected. Please contact admin for more details.';

        $user->update([
            'status' => 'rejected',
            'rejection_message' => $rejectionMessage,
        ]);

        if ($user->email) {
            Mail::to($user->email)->send(new AccountRejectedMail($user->name, $rejectionMessage));
        }

        return back()->with('status', 'Host request rejected successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->isHost(), 404);

        $user->delete();

        return back()->with('status', 'Host request deleted successfully.');
    }
}
