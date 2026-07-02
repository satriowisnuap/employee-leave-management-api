<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    /**
     * Seed the minimal roles required for tests.
     * Call this in setUp() or inside RefreshDatabase tests.
     */
    protected function seedRoles(): void
    {
        Role::firstOrCreate(['name' => 'Admin',    'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
    }
}
