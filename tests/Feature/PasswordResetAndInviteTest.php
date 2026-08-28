<?php

namespace Tests\Feature;

use App\Models\Election;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PasswordResetAndInviteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'http://localhost');
        URL::forceRootUrl('http://localhost');
    }

    public function test_voter_can_reset_password_by_contact_number(): void
    {
        User::query()->create([
            'name' => 'Voter User',
            'contact_number' => '9800000300',
            'password' => 'oldpassword1',
            'role' => 'user',
            'status' => 'approved',
        ]);

        $requestResponse = $this->post(route('password.email'), ['login' => '9800000300']);
        $requestResponse->assertRedirect();

        $location = $requestResponse->headers->get('Location');
        $path = (string) parse_url($location, PHP_URL_PATH);
        $token = basename($path);

        $this->assertNotEmpty($token);

        $updateResponse = $this->post(route('password.update'), [
            'token' => $token,
            'login' => '9800000300',
            'password' => 'newpassword1',
            'password_confirmation' => 'newpassword1',
        ]);

        $updateResponse->assertRedirect(route('login'));

        $this->post('/login', [
            'login' => '9800000300',
            'password' => 'newpassword1',
        ])->assertRedirect(route('vote.index'));
    }

    public function test_password_reset_rejects_invalid_token(): void
    {
        User::query()->create([
            'name' => 'Voter User',
            'contact_number' => '9800000301',
            'password' => 'oldpassword1',
            'role' => 'user',
            'status' => 'approved',
        ]);

        $this->post(route('password.email'), ['login' => '9800000301']);

        $response = $this->post(route('password.update'), [
            'token' => 'not-the-real-token',
            'login' => '9800000301',
            'password' => 'newpassword1',
            'password_confirmation' => 'newpassword1',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertTrue(auth()->attempt(['contact_number' => '9800000301', 'password' => 'oldpassword1']));
    }

    public function test_invite_link_preselects_and_locks_election(): void
    {
        $host = User::query()->create([
            'name' => 'Host User',
            'contact_number' => '9800000302',
            'email' => 'invitehost@example.com',
            'password' => 'password123',
            'role' => 'host',
            'status' => 'approved',
        ]);

        $election = Election::query()->create([
            'host_id' => $host->id,
            'name' => 'Invite Election',
        ]);
        $election->place()->create(['name' => 'North Campus']);
        $election->refresh();

        $response = $this->get(route('register.invite', $election->invite_token));

        $response->assertOk();
        $response->assertSee('Invite Election');
        $response->assertSee('This invite link is locked to this election.');

        $this->assertNotEmpty($election->invite_token);
    }

    public function test_invite_link_with_unknown_token_returns_404(): void
    {
        $this->get(route('register.invite', 'not-a-real-token'))->assertNotFound();
    }
}
