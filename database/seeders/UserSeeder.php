<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            [
                'email' => 'admin@example.com',
            ],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole('Admin');

        $employee = User::firstOrCreate(
            [
                'email' => 'employee@example.com',
            ],
            [
                'name' => 'Employee User',
                'password' => Hash::make('password'),
            ]
        );

        $employee->assignRole('Employee');
    }
}
