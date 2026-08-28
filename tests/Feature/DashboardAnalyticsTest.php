<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'http://localhost');
        URL::forceRootUrl('http://localhost');
    }

    public function test_admin_dashboard_shows_system_wide_analytics(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'contact_number' => '9800000200',
            'password' => 'password123',
            'role' => 'admin',
            'status' => 'approved',
        ]);

        [$hostA, $electionA] = $this->createHostWithElection('9800000201', 'hostA@example.com', 'Election A');
        [$hostB, $electionB] = $this->createHostWithElection('9800000202', 'hostB@example.com', 'Election B');

        $this->createApprovedVoter($electionA, '9800000210', hasVoted: true);
        $this->createApprovedVoter($electionB, '9800000211', hasVoted: false);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('System Analytics');
        $response->assertViewHas('dashboardAnalytics', function (array $analytics) {
            return $analytics['summary']['approved_voters'] === 2
                && $analytics['summary']['voted_voters'] === 1;
        });
    }

    public function test_host_dashboard_analytics_scoped_to_own_elections(): void
    {
        [$hostA, $electionA] = $this->createHostWithElection('9800000221', 'hostA2@example.com', 'Host A Election');
        [$hostB, $electionB] = $this->createHostWithElection('9800000222', 'hostB2@example.com', 'Host B Election');

        $this->createApprovedVoter($electionA, '9800000230', hasVoted: true);
        $this->createApprovedVoter($electionB, '9800000231', hasVoted: true);
        $this->createApprovedVoter($electionB, '9800000232', hasVoted: false);

        $response = $this->actingAs($hostA)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Host Analytics');
        $response->assertViewHas('dashboardAnalytics', function (array $analytics) {
            return $analytics['summary']['approved_voters'] === 1
                && $analytics['summary']['voted_voters'] === 1;
        });
    }

    private function createHostWithElection(string $contact, string $email, string $electionName): array
    {
        $host = User::query()->create([
            'name' => 'Host User',
            'contact_number' => $contact,
            'email' => $email,
            'password' => 'password123',
            'role' => 'host',
            'status' => 'approved',
        ]);

        $election = Election::query()->create([
            'host_id' => $host->id,
            'name' => $electionName,
        ]);

        $election->place()->create(['name' => 'Main Campus']);

        $candidate = Candidate::query()->create([
            'election_id' => $election->id,
            'name' => 'Candidate '.$electionName,
            'age' => 25,
            'position' => 'President',
            'email' => strtolower(str_replace(' ', '.', $electionName)).'@candidates.local',
            'is_active' => true,
        ]);

        return [$host, $election, $candidate];
    }

    private function createApprovedVoter(Election $election, string $contact, bool $hasVoted): User
    {
        $candidate = $election->candidates()->first();

        $user = User::query()->create([
            'name' => 'Voter '.$contact,
            'contact_number' => $contact,
            'password' => 'password123',
            'role' => 'user',
            'status' => 'approved',
            'election_id' => $election->id,
            'has_voted_at' => $hasVoted ? now() : null,
        ]);

        if ($hasVoted && $candidate) {
            Vote::query()->create([
                'user_id' => $user->id,
                'election_id' => $election->id,
                'candidate_id' => $candidate->id,
                'position' => $candidate->position,
            ]);
        }

        return $user;
    }
}
