<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

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
                'password' => 'password',
            ]
        );

        $admin->assignRole('Admin');

        $employee = User::firstOrCreate(
            [
                'email' => 'employee@example.com',
            ],
            [
                'name' => 'Employee',
                'password' => 'password',
            ]
        );

        $employee->assignRole('Employee');
    }
}
