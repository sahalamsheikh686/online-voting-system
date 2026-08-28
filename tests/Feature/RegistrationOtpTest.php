<?php

namespace Tests\Feature;

use App\Mail\RegistrationOtpMail;
use App\Models\Election;
use App\Models\RegistrationOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RegistrationOtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'http://localhost');
        URL::forceRootUrl('http://localhost');
    }

    public function test_correct_otp_creates_pending_user_and_clears_pending_registration(): void
    {
        Storage::fake('public');
        Mail::fake();
        $election = Election::query()->create(['name' => 'Biratnagar']);

        $this->post('/register', $this->validPayload($election, [
            'contact_number' => '9800000401',
            'email' => 'otp.success@example.com',
        ]));

        $otp = null;
        Mail::assertSent(RegistrationOtpMail::class, function (RegistrationOtpMail $mail) use (&$otp) {
            $otp = $mail->otp;

            return true;
        });

        $this->assertNotNull($otp);

        $response = $this->post(route('register.otp.store'), [
            'email' => 'otp.success@example.com',
            'otp' => $otp,
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', [
            'contact_number' => '9800000401',
            'email' => 'otp.success@example.com',
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('registration_otps', [
            'email' => 'otp.success@example.com',
        ]);
    }

    public function test_wrong_otp_is_rejected_and_does_not_create_user(): void
    {
        Storage::fake('public');
        Mail::fake();
        $election = Election::query()->create(['name' => 'Dharan']);

        $this->post('/register', $this->validPayload($election, [
            'contact_number' => '9800000402',
            'email' => 'otp.wrong@example.com',
        ]));

        $response = $this->post(route('register.otp.store'), [
            'email' => 'otp.wrong@example.com',
            'otp' => '000000',
        ]);

        $response->assertSessionHasErrors('otp');
        $this->assertDatabaseMissing('users', [
            'contact_number' => '9800000402',
        ]);
        $this->assertDatabaseHas('registration_otps', [
            'email' => 'otp.wrong@example.com',
            'attempts' => 1,
        ]);
    }

    public function test_expired_otp_is_rejected(): void
    {
        Storage::fake('public');
        $election = Election::query()->create(['name' => 'Butwal']);

        $pending = RegistrationOtp::query()->create([
            'email' => 'otp.expired@example.com',
            'name' => 'Expired User',
            'contact_number' => '9800000403',
            'password' => Hash::make('password123'),
            'date_of_birth' => now()->subYears(20)->toDateString(),
            'election_id' => $election->id,
            'image_path' => 'users/fake.png',
            'otp' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->subMinutes(5),
        ]);

        $response = $this->post(route('register.otp.store'), [
            'email' => 'otp.expired@example.com',
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('register'));
        $this->assertDatabaseMissing('registration_otps', ['id' => $pending->id]);
        $this->assertDatabaseMissing('users', ['contact_number' => '9800000403']);
    }

    public function test_resend_otp_issues_a_new_code(): void
    {
        Storage::fake('public');
        Mail::fake();
        $election = Election::query()->create(['name' => 'Nepalgunj']);

        $this->post('/register', $this->validPayload($election, [
            'contact_number' => '9800000404',
            'email' => 'otp.resend@example.com',
        ]));

        $firstOtpHash = RegistrationOtp::query()->where('email', 'otp.resend@example.com')->value('otp');

        $this->post(route('register.otp.resend'), ['email' => 'otp.resend@example.com'])
            ->assertRedirect();

        $secondOtpHash = RegistrationOtp::query()->where('email', 'otp.resend@example.com')->value('otp');

        $this->assertNotSame($firstOtpHash, $secondOtpHash);
        Mail::assertSent(RegistrationOtpMail::class, 2);
    }

    private function validPayload(Election $election, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'contact_number' => '9800000400',
            'email' => 'default.otp@example.com',
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
