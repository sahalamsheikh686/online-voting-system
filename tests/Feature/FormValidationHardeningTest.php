<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class FormValidationHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'http://localhost');
        URL::forceRootUrl('http://localhost');
    }

    public function test_election_name_rejects_numbers(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('elections.store'), [
            'name' => 'Election123',
            'election_title' => 'Valid Title',
            'place_name' => 'Main Hall',
        ])->assertSessionHasErrors('name');
    }

    public function test_election_title_rejects_numbers_only(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('elections.store'), [
            'name' => 'Valid Election Name',
            'election_title' => '123456',
            'place_name' => 'Main Hall',
        ])->assertSessionHasErrors('election_title');
    }

    public function test_election_title_allows_letters_and_numbers_combined(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('elections.store'), [
            'name' => 'Valid Election Name',
            'election_title' => 'Election 2026',
            'place_name' => 'Main Hall',
        ])->assertSessionDoesntHaveErrors(['name', 'election_title', 'place_name']);
    }

    public function test_candidate_party_column_no_longer_exists(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('candidates', 'party'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('deleted_candidates', 'party'));
    }

    public function test_candidate_model_does_not_accept_party(): void
    {
        $this->assertNotContains('party', (new Candidate())->getFillable());
    }

    private function makeAdmin(): User
    {
        return User::query()->create([
            'name' => 'Admin User',
            'contact_number' => '9800004000',
            'password' => 'password123',
            'role' => 'admin',
            'status' => 'approved',
        ]);
    }
}
