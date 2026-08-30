<?php

namespace Tests\Feature;

use App\Models\Election;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'http://localhost');
        URL::forceRootUrl('http://localhost');
    }

    public function test_admin_notification_counts_pending_host_requests(): void
    {
        $admin = $this->makeAdmin();

        User::query()->create([
            'name' => 'Pending Host',
            'contact_number' => '9800003001',
            'email' => 'pendinghost@example.com',
            'password' => 'password123',
            'role' => 'host',
            'status' => 'pending',
        ]);

        User::query()->create([
            'name' => 'Approved Host',
            'contact_number' => '9800003002',
            'email' => 'approvedhost@example.com',
            'password' => 'password123',
            'role' => 'host',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('app-notification-badge', false);
        $response->assertSeeInOrder(['app-notification-badge', '>1<'], false);
    }

    public function test_host_notification_only_counts_own_election_pending_voters(): void
    {
        $hostA = $this->makeHost('9800003011', 'hosta@example.com');
        $hostB = $this->makeHost('9800003012', 'hostb@example.com');

        $electionA = Election::query()->create(['host_id' => $hostA->id, 'name' => 'Election A']);
        $electionB = Election::query()->create(['host_id' => $hostB->id, 'name' => 'Election B']);

        User::query()->create([
            'name' => 'Voter A1', 'contact_number' => '9800003021',
            'password' => 'password123', 'role' => 'user', 'status' => 'pending',
            'election_id' => $electionA->id,
        ]);
        User::query()->create([
            'name' => 'Voter A2', 'contact_number' => '9800003022',
            'password' => 'password123', 'role' => 'user', 'status' => 'pending',
            'election_id' => $electionA->id,
        ]);
        User::query()->create([
            'name' => 'Voter B1', 'contact_number' => '9800003031',
            'password' => 'password123', 'role' => 'user', 'status' => 'pending',
            'election_id' => $electionB->id,
        ]);

        $responseA = $this->actingAs($hostA)->get(route('dashboard'));
        $responseA->assertOk();
        $responseA->assertSeeInOrder(['app-notification-badge', '>2<'], false);

        $responseB = $this->actingAs($hostB)->get(route('dashboard'));
        $responseB->assertOk();
        $responseB->assertSeeInOrder(['app-notification-badge', '>1<'], false);
    }

    private function makeAdmin(): User
    {
        return User::query()->create([
            'name' => 'Admin User',
            'contact_number' => '9800003000',
            'password' => 'password123',
            'role' => 'admin',
            'status' => 'approved',
        ]);
    }

    private function makeHost(string $contact, string $email): User
    {
        return User::query()->create([
            'name' => 'Host User',
            'contact_number' => $contact,
            'email' => $email,
            'password' => 'password123',
            'role' => 'host',
            'status' => 'approved',
        ]);
    }
}
