<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Election;
use App\Models\ElectionSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ElectionController extends Controller
{
    public function create(): View
    {
        return view('elections.create', [
            'elections' => $this->scopedElectionQuery()->with('place')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
            'election_title' => ['required', 'string', 'max:120', 'regex:/^[\pL\pN ]+$/u', 'not_regex:/^[\pN\s]+$/u'],
            'place_name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s]+$/', 'not_regex:/^\d+$/'],
        ], [
            'name.regex' => 'Election name must contain only alphabet characters and spaces.',
            'election_title.required' => 'Please enter the election title.',
            'election_title.regex' => 'Election title can contain only letters, numbers, and spaces.',
            'election_title.not_regex' => 'Election title cannot be numbers only.',
            'place_name.required' => 'Please enter the election place.',
            'place_name.regex' => 'Election place can contain only letters, numbers, and spaces.',
            'place_name.not_regex' => 'Election place cannot be numbers only.',
        ]);

        $electionName = trim($validated['name']);
        $placeName = trim($validated['place_name']);
        $hostId = auth()->user()->isHost() ? auth()->id() : null;

        if ($hostId && Election::query()->where('host_id', $hostId)->where('name', '!=', $electionName)->exists()) {
            throw ValidationException::withMessages([
                'name' => 'Host can use only one election name. Add another election place under your existing election name.',
            ]);
        }

        if (Election::query()
            ->where('name', $electionName)
            ->when($hostId, fn ($query) => $query->where('host_id', $hostId))
            ->when(! $hostId, fn ($query) => $query->whereNull('host_id'))
            ->whereHas('place', fn ($query) => $query->where('name', $placeName))
            ->exists()) {
            throw ValidationException::withMessages([
                'place_name' => 'This election place already has an election card.',
            ]);
        }

        DB::transaction(function () use ($electionName, $placeName, $validated, $hostId) {
            $election = Election::create([
                'host_id' => $hostId,
                'name' => $electionName,
            ]);

            $election->place()->create([
                'name' => $placeName,
            ]);

            ElectionSetting::firstOrCreate(
                ['election_id' => $election->id],
                [
                    'election_title' => trim($validated['election_title']),
                    'is_active' => false,
                    'started_at' => null,
                    'ends_at' => null,
                ]
            )->update([
                'election_title' => trim($validated['election_title']),
            ]);
        });

        return redirect()->route('elections.create')->with('status', 'Election card added successfully.');
    }

    public function hardDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'election_id' => ['required', 'exists:elections,id'],
        ], [
            'election_id.required' => 'Please select an election card to delete.',
        ]);

        $election = Election::query()->findOrFail((int) $validated['election_id']);
        $this->authorizeElectionAccess($election);
        $electionLabel = $election->place?->name ? "{$election->name} - {$election->place->name}" : $election->name;

        DB::transaction(function () use ($election) {
            User::query()
                ->where('election_id', $election->id)
                ->update([
                    'election_id' => null,
                    'last_known_election_name' => null,
                    'has_voted_at' => null,
                ]);

            AuditLog::query()->where('election_id', $election->id)->delete();
            ElectionSetting::query()->where('election_id', $election->id)->delete();
            $election->delete();
        });

        return redirect()
            ->route('elections.create')
            ->with('status', "{$electionLabel} election card was permanently deleted from the system.");
    }

    private function scopedElectionQuery()
    {
        return Election::query()
            ->when(auth()->user()->isHost(), fn ($query) => $query->where('host_id', auth()->id()));
    }

    private function authorizeElectionAccess(Election $election): void
    {
        if (auth()->user()->isHost() && (int) $election->host_id !== (int) auth()->id()) {
            abort(403);
        }
    }
}
