<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'super@ticko-plug.co.ke'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('P@ssw0rd123!'),
                'phone'    => '+254700000001',
                'status'   => UserStatus::Active,
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('super_admin');

        // Platform Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@ticko-plug.co.ke'],
            [
                'name'     => 'Platform Admin',
                'password' => Hash::make('P@ssw0rd123!'),
                'phone'    => '+254700000002',
                'status'   => UserStatus::Active,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // Demo Organizer
        $organizer = User::firstOrCreate(
            ['email' => 'organizer@demo.co.ke'],
            [
                'name'     => 'Demo Organizer',
                'password' => Hash::make('P@ssw0rd123!'),
                'phone'    => '+254711000001',
                'status'   => UserStatus::Active,
                'email_verified_at' => now(),
            ]
        );
        $organizer->assignRole('organizer');

        // Create organizer profile
        if (! $organizer->organizerProfile) {
            $organizer->organizerProfile()->create([
                'organization_name' => 'Demo Events Kenya',
                'slug'              => 'demo-events-kenya',
                'description'       => 'Demo organizer account for Ticko-Plug testing.',
                'payout_schedule'   => 'weekly',
            ]);
        }

        $this->command->info('Admin users seeded. Login: super@ticko-plug.co.ke / P@ssw0rd123!');
    }
}
