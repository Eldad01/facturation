<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@facturation.com'],
            [
                'name' => 'Admin',
                'email' => 'admin@facturation.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@facturation.com'],
            [
                'name' => 'Utilisateur',
                'email' => 'user@facturation.com',
                'password' => Hash::make('user123'),
                'role' => 'employe',
                'is_active' => true,
            ]
        );
    }
}
