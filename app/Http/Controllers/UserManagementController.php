<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use App\Models\Election;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $electionId = request('election_id');
        $showAll = request()->boolean('show_all');

        $users = User::query()
            ->with('election.place')
            ->where('role', 'user')
            ->where('status', 'approved')
            ->when(auth()->user()->isHost(), fn ($query) => $query->whereHas('election', fn ($electionQuery) => $electionQuery->where('host_id', auth()->id())))
            ->when($electionId, fn ($query) => $query->where('election_id', $electionId))
            ->when(
                $showAll,
                fn ($query) => $query
                    ->join('elections', 'elections.id', '=', 'users.election_id')
                    ->orderBy('elections.name')
                    ->orderBy('users.name')
                    ->select('users.*'),
                fn ($query) => $query->orderBy('name')
            )
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'elections' => Election::query()
                ->with('place')
                ->when(auth()->user()->isHost(), fn ($query) => $query->where('host_id', auth()->id()))
                ->orderBy('name')
                ->get(),
            'selectedElection' => $electionId,
            'showAll' => $showAll,
        ]);
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $this->authorizeUserAccess($user);

        $data = $request->validated();

        if (isset($data['election_id'])) {
            $data['last_known_election_name'] = Election::query()->find($data['election_id'])?->name;
        }

        $user->update($data);

        return back()->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeUserAccess($user);

        $user->delete();

        return back()->with('status', 'User deleted successfully.');
    }

    private function authorizeUserAccess(User $user): void
    {
        if (auth()->user()->isHost() && (int) $user->election?->host_id !== (int) auth()->id()) {
            abort(403);
        }
    }
}
