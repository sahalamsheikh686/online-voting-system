<?php

namespace Database\Seeders;

use App\Models\AdminProfile;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $elections = collect([
            'Student Council Election',
            'Class Representative Election',
            'Faculty Board Election',
            'Club Leadership Election',
            'Community Committee Election',
            'Sports Council Election',
            'Project Team Election',
            'Batch Monitor Election',
            'Department Lead Election',
            'Union Panel Election',
            'House Captain Election',
            'Event Committee Election',
            'Youth Forum Election',
        ])->map(fn (string $name) => Election::firstOrCreate(['name' => $name]));

        $admin = User::updateOrCreate(
            ['contact_number' => '9800000000'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('admin12345'),
                'role' => 'admin',
                'status' => 'approved',
                'approved_at' => now(),
            ]
        );

        AdminProfile::updateOrCreate(
            ['user_id' => $admin->id],
            ['age' => 35, 'contact_number' => '9800000000']
        );

        $positions = [
            1 => 'President',
            2 => 'President',
            3 => 'Vice President',
            4 => 'Vice President',
        ];
        $parties = ['Unity Party', 'Forward Nepal', 'Citizen Forum', 'Independent'];

        foreach ($elections as $index => $election) {
            foreach (range(1, 4) as $candidateNumber) {
                Candidate::updateOrCreate(
                    [
                        'election_id' => $election->id,
                        'email' => "candidate{$candidateNumber}{$index}@example.com",
                    ],
                    [
                        'name' => fake()->unique()->firstName().' '.$election->name,
                        'party' => Arr::random($parties),
                        'age' => fake()->numberBetween(35, 68),
                        'position' => $positions[$candidateNumber],
                        'is_active' => true,
                    ]
                );
            }
        }

        $approvedElection = $elections->first();
        $pendingElection = $elections->get(1);
        $rejectedElection = $elections->get(2);

        User::updateOrCreate(
            ['contact_number' => '9811111111'],
            [
                'name' => 'Approved User',
                'password' => Hash::make('user12345'),
                'role' => 'user',
                'status' => 'approved',
                'approved_at' => now(),
                'date_of_birth' => '1998-04-17',
                'election_id' => $approvedElection?->id,
            ]
        );

        User::updateOrCreate(
            ['contact_number' => '9822222222'],
            [
                'name' => 'Pending User',
                'password' => Hash::make('user12345'),
                'role' => 'user',
                'status' => 'pending',
                'date_of_birth' => '2000-08-10',
                'election_id' => $pendingElection?->id,
            ]
        );

        User::updateOrCreate(
            ['contact_number' => '9833333333'],
            [
                'name' => 'Rejected User',
                'password' => Hash::make('user12345'),
                'role' => 'user',
                'status' => 'rejected',
                'date_of_birth' => '2001-01-19',
                'election_id' => $rejectedElection?->id,
                'rejection_message' => 'You can try once again',
            ]
        );

        foreach ($elections as $election) {
            ElectionSetting::updateOrCreate(
                ['election_id' => $election->id],
                [
                    'is_active' => true,
                    'ended_at' => null,
                    'ends_at' => now()->addDays(2),
                ]
            );
        }
    }
}
