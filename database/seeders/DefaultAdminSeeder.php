<?php

namespace Database\Seeders;

use App\Models\AdminProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class DefaultAdminSeeder extends Seeder
{
    public function run(): void
    {
        $contactNumber = env('DEFAULT_ADMIN_CONTACT', '9800000000');

        $admin = User::firstOrCreate(
            ['contact_number' => $contactNumber],
            [
                'name' => env('DEFAULT_ADMIN_NAME', 'System Admin'),
                'password' => env('DEFAULT_ADMIN_PASSWORD', 'admin12345'),
                'role' => 'admin',
                'status' => 'approved',
                'approved_at' => now(),
            ]
        );

        AdminProfile::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'age' => (int) env('DEFAULT_ADMIN_AGE', 35),
                'contact_number' => $contactNumber,
            ]
        );
    }
}
