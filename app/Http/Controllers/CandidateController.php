<?php

namespace App\Http\Controllers;

use App\Http\Requests\CandidateRequest;
use App\Models\Candidate;
use App\Models\DeletedCandidate;
use App\Models\Election;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CandidateController extends Controller
{
    public function index(): View
    {
        $electionId = request('election_id');

        $candidates = Candidate::query()
            ->with('election.place')
            ->when($electionId, fn ($query) => $query->where('election_id', $electionId))
            ->when(auth()->user()->isHost(), fn ($query) => $query->whereHas('election', fn ($electionQuery) => $electionQuery->where('host_id', auth()->id())))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('candidates.index', [
            'candidates' => $candidates,
            'elections' => Election::query()
                ->with('place')
                ->when(auth()->user()->isHost(), fn ($query) => $query->where('host_id', auth()->id()))
                ->orderBy('name')
                ->get(),
            'selectedElection' => $electionId,
        ]);
    }

    public function create(): View
    {
        return view('candidates.form', [
            'candidate' => new Candidate(),
            'elections' => $this->electionGroups(),
            'formAction' => route('candidates.store'),
            'formMethod' => 'POST',
            'pageTitle' => 'Add Candidate',
        ]);
    }

    public function store(CandidateRequest $request): RedirectResponse
    {
        $candidate = new Candidate($request->safe()->except(['image', 'vision', 'election_name']));
        $candidate->image_path = $request->file('image')?->store('candidates/images', 'public');
        $candidate->vision_path = $request->file('vision')?->store('candidates/visions', 'public');
        $candidate->position = $request->input('position', 'Election Representative');
        $candidate->save();

        return redirect()->route('candidates.index')->with('status', 'Candidate added successfully.');
    }

    public function edit(Candidate $candidate): View
    {
        $this->authorizeCandidateAccess($candidate);

        return view('candidates.form', [
            'candidate' => $candidate,
            'elections' => $this->electionGroups(),
            'formAction' => route('candidates.update', $candidate),
            'formMethod' => 'PUT',
            'pageTitle' => 'Edit Candidate',
        ]);
    }

    public function update(CandidateRequest $request, Candidate $candidate): RedirectResponse
    {
        $this->authorizeCandidateAccess($candidate);

        $data = $request->safe()->except(['image', 'vision', 'election_name']);
        $data['position'] = $request->input('position', 'Election Representative');

        if ($request->hasFile('image')) {
            if ($candidate->image_path) {
                Storage::disk('public')->delete($candidate->image_path);
            }

            $data['image_path'] = $request->file('image')->store('candidates/images', 'public');
        }

        if ($request->hasFile('vision')) {
            if ($candidate->vision_path) {
                Storage::disk('public')->delete($candidate->vision_path);
            }

            $data['vision_path'] = $request->file('vision')->store('candidates/visions', 'public');
        }

        $candidate->update($data);

        return redirect()->route('candidates.index')->with('status', 'Candidate updated successfully.');
    }

    public function destroy(Candidate $candidate): RedirectResponse
    {
        $this->authorizeCandidateAccess($candidate);

        DeletedCandidate::query()->create([
            'original_candidate_id' => $candidate->id,
            'election_name' => $candidate->election?->name ?? 'Unknown Election',
            'candidate_name' => $candidate->name,
            'age' => $candidate->age,
            'position' => $candidate->position,
            'email' => $candidate->email,
            'image_path' => $candidate->image_path,
            'vision_path' => $candidate->vision_path,
            'vote_count' => $candidate->votes()->count(),
            'deleted_reason' => 'candidate_deleted',
            'deleted_at' => now(),
        ]);

        if ($candidate->image_path) {
            Storage::disk('public')->delete($candidate->image_path);
        }

        if ($candidate->vision_path) {
            Storage::disk('public')->delete($candidate->vision_path);
        }

        $candidate->delete();

        return redirect()->route('candidates.index')->with('status', 'Candidate deleted successfully.');
    }

    private function electionGroups()
    {
        return Election::query()
            ->with('place')
            ->when(auth()->user()->isHost(), fn ($query) => $query->where('host_id', auth()->id()))
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

    private function authorizeCandidateAccess(Candidate $candidate): void
    {
        if (auth()->user()->isHost() && (int) $candidate->election?->host_id !== (int) auth()->id()) {
            abort(403);
        }
    }
}
