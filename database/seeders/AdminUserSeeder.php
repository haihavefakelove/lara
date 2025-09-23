<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('password'), 
                'role'     => 'admin',
            ]
        );
    }
}
\App\Models\User::updateOrCreate(
    ['email' => 'admin@example.com'],
    [
        'name'              => 'Admin User',
        'password'          => bcrypt('secret'),
        'role'              => 'admin',
        'email_verified_at' => now(),   
    ]
);