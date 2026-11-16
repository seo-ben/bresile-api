<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@ecole-bresil.com'],
            [
                'password_hash' => Hash::make('Admin@123456'),
                'role' => 'admin',
                'status' => 'active',
                'preferred_locale' => 'fr',
                'email_verified_at' => now(),
            ]
        );

        UserProfile::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'first_name' => 'Directeur',
                'last_name' => 'Pédagogique',
                'phone' => '+55 61 9999-9999',
                'country_code' => 'BR',
            ]
        );
    }
}
