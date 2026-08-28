<?php

namespace Tests\Feature;

use App\Mail\RegistrationOtpMail;
use App\Models\Election;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RegisterValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'http://localhost');
        URL::forceRootUrl('http://localhost');
    }

    public function test_registration_rejects_underage_user(): void
    {
        Storage::fake('public');
        $election = Election::query()->create(['name' => 'Bhaktapur']);

        $response = $this->from('/register')->post('/register', $this->validPayload($election, [
            'contact_number' => '9800000004',
            'date_of_birth' => now()->subYears(17)->toDateString(),
        ]));

        $response->assertSessionHasErrors('date_of_birth');
    }

    public function test_registration_accepts_exactly_eighteen_year_old_user(): void
    {
        Storage::fake('public');
        Mail::fake();
        $election = Election::query()->create(['name' => 'Pokhara']);

        $response = $this->from('/register')->post('/register', $this->validPayload($election, [
            'contact_number' => '9800000005',
            'email' => 'eighteen@example.com',
            'date_of_birth' => now()->subYears(18)->toDateString(),
        ]));

        $response->assertRedirect(route('register.otp', ['email' => 'eighteen@example.com']));
        $this->assertDatabaseHas('registration_otps', [
            'contact_number' => '9800000005',
            'email' => 'eighteen@example.com',
            'election_id' => $election->id,
        ]);
        $this->assertDatabaseMissing('users', [
            'contact_number' => '9800000005',
        ]);
        Mail::assertSent(RegistrationOtpMail::class);
    }

    public function test_registration_rejects_mismatched_election_place(): void
    {
        Storage::fake('public');
        $selectedElection = Election::query()->create(['name' => 'Abcd']);
        Election::query()->create(['name' => 'Different Election']);

        $response = $this->from('/register')->post('/register', $this->validPayload($selectedElection, [
            'contact_number' => '9800000006',
            'election_name' => 'Different Election',
        ]));

        $response->assertSessionHasErrors('election_id');
    }

    private function validPayload(Election $election, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'contact_number' => '9800000099',
            'email' => 'test.user@example.com',
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
