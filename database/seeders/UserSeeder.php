<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@ozj.nl'],
            [
                'name' => 'Admin OZJ',
                'password' => Hash::make('password'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'stevano@ozj.nl'],
            [
                'name' => 'Stevano',
                'password' => Hash::make('password'),
            ]
        );
    }
}
