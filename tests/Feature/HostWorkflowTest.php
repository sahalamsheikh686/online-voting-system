<?php

namespace Tests\Feature;

use App\Models\Election;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class HostWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'http://localhost');
        URL::forceRootUrl('http://localhost');
    }

    public function test_host_registration_rejects_numbers_only_reason_message(): void
    {
        Storage::fake('public');

        $response = $this->from(route('hosts.create'))->post(route('hosts.store'), $this->validHostPayload([
            'reason_message' => '1234567890',
        ]));

        $response->assertSessionHasErrors('reason_message');
    }

    public function test_admin_can_accept_host_and_host_reaches_dashboard(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'contact_number' => '9800000100',
            'password' => 'password123',
            'role' => 'admin',
            'status' => 'approved',
        ]);

        $host = User::query()->create([
            'name' => 'Host User',
            'contact_number' => '9800000101',
            'email' => 'host@example.com',
            'password' => 'password123',
            'role' => 'host',
            'status' => 'pending',
            'image_path' => 'hosts/host.png',
        ]);

        $host->hostProfile()->create([
            'reason_type' => 'College',
            'reason_message' => 'I want to host election for my college faculty groups with fair voting access.',
        ]);

        $this->actingAs($admin)
            ->put(route('host-requests.update', ['user' => $host->id]), ['action' => 'accept'])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $host->id,
            'status' => 'approved',
        ]);

        $this->post('/login', [
            'login' => 'host@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_host_can_reuse_one_election_name_for_different_places_only(): void
    {
        $host = User::query()->create([
            'name' => 'Host User',
            'contact_number' => '9800000102',
            'email' => 'host2@example.com',
            'password' => 'password123',
            'role' => 'host',
            'status' => 'approved',
        ]);

        $this->actingAs($host)
            ->post(route('elections.store'), [
                'name' => 'ABC College',
                'election_title' => 'ABC College Election',
                'place_name' => 'BCA Faculty',
            ])
            ->assertRedirect(route('elections.create'));

        $this->actingAs($host)
            ->post(route('elections.store'), [
                'name' => 'ABC College',
                'election_title' => 'ABC College Election',
                'place_name' => 'BBS Faculty',
            ])
            ->assertRedirect(route('elections.create'));

        $this->actingAs($host)
            ->from(route('elections.create'))
            ->post(route('elections.store'), [
                'name' => 'XYZ College',
                'election_title' => 'XYZ College Election',
                'place_name' => 'BCA Faculty',
            ])
            ->assertSessionHasErrors('name');

        $this->actingAs($host)
            ->from(route('elections.create'))
            ->post(route('elections.store'), [
                'name' => 'ABC College',
                'election_title' => 'ABC College Election',
                'place_name' => 'BCA Faculty',
            ])
            ->assertSessionHasErrors('place_name');

        $this->assertSame(2, Election::query()->where('host_id', $host->id)->count());
    }

    private function validHostPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Host User',
            'contact_number' => '9800000199',
            'reason_type' => 'School',
            'email' => 'newhost@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'reason_message' => 'I want to host election for students and manage transparent voting safely.',
            'image' => UploadedFile::fake()->createWithContent(
                'host.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
            ),
        ], $overrides);
    }
}
