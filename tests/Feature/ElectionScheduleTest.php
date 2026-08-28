<?php

namespace Tests\Feature;

use App\Models\Election;
use App\Models\ElectionSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ElectionScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'http://localhost');
        URL::forceRootUrl('http://localhost');
    }

    public function test_scheduling_a_future_start_does_not_activate_immediately(): void
    {
        $admin = $this->makeAdmin();
        $election = Election::query()->create(['name' => 'Schedule Election']);

        $this->actingAs($admin)->post(route('dashboard.start-election', $election), [
            'election_title' => 'Schedule Election Title',
            'starts_at' => now()->addHour()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addHours(3)->format('Y-m-d\TH:i'),
        ])->assertRedirect();

        $setting = ElectionSetting::query()->where('election_id', $election->id)->first();

        $this->assertFalse($setting->is_active);
        $this->assertNotNull($setting->scheduled_start_at);
        $this->assertTrue($setting->isScheduled());
    }

    public function test_dashboard_visit_auto_activates_election_once_scheduled_time_arrives(): void
    {
        $admin = $this->makeAdmin();
        $election = Election::query()->create(['name' => 'Auto Start Election']);

        ElectionSetting::query()->create([
            'election_id' => $election->id,
            'election_title' => 'Auto Start Title',
            'is_active' => false,
            'started_at' => null,
            'scheduled_start_at' => now()->subMinute(),
            'ends_at' => now()->addHours(2),
        ]);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();

        $setting = ElectionSetting::query()->where('election_id', $election->id)->first();

        $this->assertTrue($setting->is_active);
        $this->assertNull($setting->scheduled_start_at);
        $this->assertNotNull($setting->started_at);
    }

    public function test_voting_page_auto_activates_election_once_scheduled_time_arrives(): void
    {
        $election = Election::query()->create(['name' => 'Voter Auto Start Election']);

        ElectionSetting::query()->create([
            'election_id' => $election->id,
            'election_title' => 'Voter Auto Start Title',
            'is_active' => false,
            'started_at' => null,
            'scheduled_start_at' => now()->subMinute(),
            'ends_at' => now()->addHours(2),
        ]);

        $voter = User::query()->create([
            'name' => 'Scheduled Voter',
            'contact_number' => '9800000800',
            'email' => 'scheduled.voter@example.com',
            'password' => 'password123',
            'role' => 'user',
            'status' => 'approved',
            'election_id' => $election->id,
        ]);

        $this->actingAs($voter)->get(route('vote.index'))->assertOk();

        $setting = ElectionSetting::query()->where('election_id', $election->id)->first();

        $this->assertTrue($setting->is_active);
        $this->assertNull($setting->scheduled_start_at);
    }

    public function test_scheduled_election_does_not_activate_before_its_time(): void
    {
        $admin = $this->makeAdmin();
        $election = Election::query()->create(['name' => 'Not Yet Election']);

        ElectionSetting::query()->create([
            'election_id' => $election->id,
            'election_title' => 'Not Yet Title',
            'is_active' => false,
            'started_at' => null,
            'scheduled_start_at' => now()->addHour(),
            'ends_at' => now()->addHours(3),
        ]);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();

        $setting = ElectionSetting::query()->where('election_id', $election->id)->first();

        $this->assertFalse($setting->is_active);
        $this->assertNotNull($setting->scheduled_start_at);
    }

    public function test_end_time_must_be_after_scheduled_start_time(): void
    {
        $admin = $this->makeAdmin();
        $election = Election::query()->create(['name' => 'Bad Schedule Election']);

        $this->actingAs($admin)->post(route('dashboard.start-election', $election), [
            'election_title' => 'Bad Schedule Title',
            'starts_at' => now()->addHours(3)->format('Y-m-d\TH:i'),
            'ends_at' => now()->addHour()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('ends_at');
    }

    private function makeAdmin(): User
    {
        return User::query()->create([
            'name' => 'Admin User',
            'contact_number' => '9800000801',
            'password' => 'password123',
            'role' => 'admin',
            'status' => 'approved',
        ]);
    }
}
