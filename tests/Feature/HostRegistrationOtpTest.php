<?php

namespace Tests\Feature;

use App\Mail\RegistrationOtpMail;
use App\Models\HostRegistrationOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class HostRegistrationOtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'http://localhost');
        URL::forceRootUrl('http://localhost');
    }

    public function test_host_registration_sends_otp_instead_of_creating_user_immediately(): void
    {
        Storage::fake('public');
        Mail::fake();

        $response = $this->post(route('hosts.store'), $this->validHostPayload([
            'contact_number' => '9800000700',
            'email' => 'host.otp@example.com',
        ]));

        $response->assertRedirect(route('hosts.otp', ['email' => 'host.otp@example.com']));
        $this->assertDatabaseHas('host_registration_otps', [
            'contact_number' => '9800000700',
            'email' => 'host.otp@example.com',
        ]);
        $this->assertDatabaseMissing('users', [
            'contact_number' => '9800000700',
        ]);
        Mail::assertSent(RegistrationOtpMail::class);
    }

    public function test_correct_otp_creates_pending_host_and_profile(): void
    {
        Storage::fake('public');
        Mail::fake();

        $this->post(route('hosts.store'), $this->validHostPayload([
            'contact_number' => '9800000701',
            'email' => 'host.otp2@example.com',
        ]));

        $otp = null;
        Mail::assertSent(RegistrationOtpMail::class, function (RegistrationOtpMail $mail) use (&$otp) {
            $otp = $mail->otp;

            return true;
        });

        $response = $this->post(route('hosts.otp.store'), [
            'email' => 'host.otp2@example.com',
            'otp' => $otp,
        ]);

        $response->assertRedirect(route('login'));

        $host = User::query()->where('contact_number', '9800000701')->first();

        $this->assertNotNull($host);
        $this->assertSame('host', $host->role);
        $this->assertSame('pending', $host->status);
        $this->assertNotNull($host->hostProfile);
        $this->assertDatabaseMissing('host_registration_otps', [
            'email' => 'host.otp2@example.com',
        ]);
    }

    public function test_wrong_otp_does_not_create_host(): void
    {
        Storage::fake('public');
        Mail::fake();

        $this->post(route('hosts.store'), $this->validHostPayload([
            'contact_number' => '9800000702',
            'email' => 'host.otp3@example.com',
        ]));

        $response = $this->post(route('hosts.otp.store'), [
            'email' => 'host.otp3@example.com',
            'otp' => '000000',
        ]);

        $response->assertSessionHasErrors('otp');
        $this->assertDatabaseMissing('users', ['contact_number' => '9800000702']);
        $this->assertDatabaseHas('host_registration_otps', [
            'email' => 'host.otp3@example.com',
            'attempts' => 1,
        ]);
    }

    public function test_expired_host_otp_is_rejected(): void
    {
        Storage::fake('public');

        $pending = HostRegistrationOtp::query()->create([
            'email' => 'host.expired@example.com',
            'name' => 'Expired Host',
            'contact_number' => '9800000703',
            'password' => Hash::make('password123'),
            'reason_type' => 'School',
            'reason_message' => 'I want to host election for my school with fair voting.',
            'image_path' => 'hosts/fake.png',
            'otp' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->subMinutes(5),
        ]);

        $response = $this->post(route('hosts.otp.store'), [
            'email' => 'host.expired@example.com',
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('hosts.create'));
        $this->assertDatabaseMissing('host_registration_otps', ['id' => $pending->id]);
        $this->assertDatabaseMissing('users', ['contact_number' => '9800000703']);
    }

    private function validHostPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Host User',
            'contact_number' => '9800000799',
            'reason_type' => 'School',
            'email' => 'defaulthost@example.com',
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
