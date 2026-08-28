<?php

namespace App\Http\Controllers;

use App\Http\Requests\PendingUserActionRequest;
use App\Mail\AccountRejectedMail;
use App\Models\Election;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PendingUserController extends Controller
{
    public function index(): View
    {
        $electionId = request('election_id');
        $elections = $this->scopedElectionQuery()->with('place')->orderBy('name')->get();

        return view('pending-users.index', [
            'pendingUsers' => User::query()
                ->with('election.place')
                ->where('role', 'user')
                ->where('status', 'pending')
                ->when(auth()->user()->isHost(), fn ($query) => $query->whereIn('election_id', $elections->pluck('id')))
                ->when($electionId, fn ($query) => $query->where('election_id', $electionId))
                ->orderBy('name')
                ->get(),
            'elections' => $elections,
            'selectedElection' => $electionId,
        ]);
    }

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

    private function scopedElectionQuery()
    {
        return Election::query()
            ->when(auth()->user()->isHost(), fn ($query) => $query->where('host_id', auth()->id()));
    }

    private function authorizeUserAccess(User $user): void
    {
        if (auth()->user()->isHost() && (int) $user->election?->host_id !== (int) auth()->id()) {
            abort(403);
        }
    }
}
