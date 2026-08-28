<?php

namespace Tests\Feature;

use App\Mail\RegistrationOtpMail;
use App\Models\Election;
use App\Models\RegistrationOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RejectedAccountReuseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'http://localhost');
        URL::forceRootUrl('http://localhost');
    }

    public function test_rejected_voter_contact_and_email_can_be_reused(): void
    {
        Storage::fake('public');
        Mail::fake();
        $election = Election::query()->create(['name' => 'Reuse Election']);

        User::query()->create([
            'name' => 'First Attempt',
            'contact_number' => '9800000501',
            'email' => 'reuse.voter@example.com',
            'password' => 'password123',
            'role' => 'user',
            'status' => 'rejected',
            'rejection_message' => 'Photo unclear',
            'election_id' => $election->id,
        ]);

        $response = $this->post('/register', $this->validPayload($election, [
            'name' => 'Second Attempt',
            'contact_number' => '9800000501',
            'email' => 'reuse.voter@example.com',
        ]));

        $response->assertSessionDoesntHaveErrors(['contact_number', 'email']);
        $response->assertRedirect(route('register.otp', ['email' => 'reuse.voter@example.com']));

        $otp = null;
        Mail::assertSent(RegistrationOtpMail::class, function (RegistrationOtpMail $mail) use (&$otp) {
            $otp = $mail->otp;

            return true;
        });

        $this->post(route('register.otp.store'), [
            'email' => 'reuse.voter@example.com',
            'otp' => $otp,
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'contact_number' => '9800000501',
            'email' => 'reuse.voter@example.com',
            'name' => 'Second Attempt',
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('users', [
            'name' => 'First Attempt',
        ]);
        $this->assertSame(1, User::query()->where('contact_number', '9800000501')->count());
    }

    public function test_approved_voter_contact_and_email_cannot_be_reused(): void
    {
        Storage::fake('public');
        $election = Election::query()->create(['name' => 'Locked Election']);

        User::query()->create([
            'name' => 'Approved Voter',
            'contact_number' => '9800000502',
            'email' => 'locked.voter@example.com',
            'password' => 'password123',
            'role' => 'user',
            'status' => 'approved',
            'election_id' => $election->id,
        ]);

        $response = $this->post('/register', $this->validPayload($election, [
            'contact_number' => '9800000502',
            'email' => 'locked.voter@example.com',
        ]));

        $response->assertSessionHasErrors(['contact_number', 'email']);
    }

    public function test_rejected_host_contact_and_email_can_be_reused(): void
    {
        Storage::fake('public');
        Mail::fake();

        User::query()->create([
            'name' => 'Old Host',
            'contact_number' => '9800000503',
            'email' => 'reuse.host@example.com',
            'password' => 'password123',
            'role' => 'host',
            'status' => 'rejected',
            'rejection_message' => 'Not enough details',
        ]);

        $response = $this->post(route('hosts.store'), [
            'name' => 'New Host',
            'contact_number' => '9800000503',
            'reason_type' => 'School',
            'email' => 'reuse.host@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'reason_message' => 'I want to host election for my students and manage transparent voting.',
            'image' => UploadedFile::fake()->createWithContent(
                'host.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
            ),
        ]);

        $response->assertSessionDoesntHaveErrors(['contact_number', 'email']);
        $response->assertRedirect(route('hosts.otp', ['email' => 'reuse.host@example.com']));

        $otp = null;
        Mail::assertSent(RegistrationOtpMail::class, function (RegistrationOtpMail $mail) use (&$otp) {
            $otp = $mail->otp;

            return true;
        });

        $this->post(route('hosts.otp.store'), [
            'email' => 'reuse.host@example.com',
            'otp' => $otp,
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'contact_number' => '9800000503',
            'email' => 'reuse.host@example.com',
            'name' => 'New Host',
            'status' => 'pending',
        ]);
        $this->assertSame(1, User::query()->where('contact_number', '9800000503')->count());
    }

    private function validPayload(Election $election, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'contact_number' => '9800000500',
            'email' => 'default.reuse@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'date_of_birth' => '2000-01-01',
            'election_name' => $election->name,
            'election_id' => $election->id,
            'image' => UploadedFile::fake()->createWithContent(
                'user.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
            ),
        ], $overrides);
    }
}
