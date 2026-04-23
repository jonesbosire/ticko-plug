<?php

namespace App\Filament\Organizer\Pages;

use App\Enums\UserStatus;
use App\Models\OrganizerProfile;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;

class Register extends BaseRegister
{
    protected function handleRegistration(array $data): Model
    {
        $user = $this->getUserModel()::create([
            'name'               => $data['name'],
            'email'              => $data['email'],
            'password'           => $data['password'],
            'status'             => UserStatus::Active,
            'email_verified_at'  => now(),
        ]);

        $user->assignRole('organizer');

        // Bootstrap an empty organizer profile so the panel doesn't 404
        OrganizerProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'organization_name' => $user->name,
                'payout_schedule'   => 'monthly',
            ]
        );

        return $user;
    }
}
