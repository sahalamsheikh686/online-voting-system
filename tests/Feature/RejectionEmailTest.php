<?php

namespace Tests\Feature;

use App\Mail\AccountRejectedMail;
use App\Models\Election;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RejectionEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'http://localhost');
        URL::forceRootUrl('http://localhost');
    }

    public function test_rejecting_a_pending_voter_emails_the_rejection_reason(): void
    {
        Mail::fake();

        $admin = User::query()->create([
            'name' => 'Admin User',
            'contact_number' => '9800000600',
            'password' => 'password123',
            'role' => 'admin',
            'status' => 'approved',
        ]);

        $election = Election::query()->create(['name' => 'Rejection Test Election']);

        $voter = User::query()->create([
            'name' => 'Pending Voter',
            'contact_number' => '9800000601',
            'email' => 'pending.voter@example.com',
            'password' => 'password123',
            'role' => 'user',
            'status' => 'pending',
            'election_id' => $election->id,
        ]);

        $this->actingAs($admin)
            ->put(route('pending-users.update', $voter), [
                'action' => 'reject',
                'rejection_message' => 'Your photo was blurry, please retry.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $voter->id,
            'status' => 'rejected',
            'rejection_message' => 'Your photo was blurry, please retry.',
        ]);

        Mail::assertSent(AccountRejectedMail::class, function (AccountRejectedMail $mail) {
            return $mail->hasTo('pending.voter@example.com')
                && $mail->reason === 'Your photo was blurry, please retry.';
        });
    }

    public function test_rejecting_a_host_request_emails_the_rejection_reason(): void
    {
        Mail::fake();

        $admin = User::query()->create([
            'name' => 'Admin User',
            'contact_number' => '9800000602',
            'password' => 'password123',
            'role' => 'admin',
            'status' => 'approved',
        ]);

        $host = User::query()->create([
            'name' => 'Pending Host',
            'contact_number' => '9800000603',
            'email' => 'pending.host@example.com',
            'password' => 'password123',
            'role' => 'host',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->put(route('host-requests.update', $host), [
                'action' => 'reject',
                'rejection_message' => 'Please provide more details about your election plan.',
            ])
            ->assertRedirect();

        Mail::assertSent(AccountRejectedMail::class, function (AccountRejectedMail $mail) {
            return $mail->hasTo('pending.host@example.com')
                && $mail->reason === 'Please provide more details about your election plan.';
        });
    }
}
