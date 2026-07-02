<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    // ─────────────────────────────────────────────────────────────────
    // Employee cannot access Admin endpoints
    // ─────────────────────────────────────────────────────────────────

    public function test_employee_tidak_dapat_akses_endpoint_admin(): void
    {
        $employee = User::factory()->employee()->create();
        $token = $employee->createToken('auth-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/admin/leaves')
            ->assertForbidden();
    }

    public function test_employee_tidak_dapat_approve_cuti(): void
    {
        $employee = User::factory()->employee()->create();
        $leave = LeaveRequest::factory()->create(['user_id' => $employee->id]);
        $token = $employee->createToken('auth-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/admin/leaves/{$leave->id}/approve")
            ->assertForbidden();
    }

    public function test_employee_tidak_dapat_reject_cuti(): void
    {
        $employee = User::factory()->employee()->create();
        $leave = LeaveRequest::factory()->create(['user_id' => $employee->id]);
        $token = $employee->createToken('auth-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/admin/leaves/{$leave->id}/reject")
            ->assertForbidden();
    }

    // ─────────────────────────────────────────────────────────────────
    // Admin can access all Admin endpoints
    // ─────────────────────────────────────────────────────────────────

    public function test_admin_dapat_akses_seluruh_endpoint_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('auth-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/admin/leaves')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_admin_dapat_melihat_seluruh_pengajuan_cuti(): void
    {
        $admin = User::factory()->admin()->create();
        $employee = User::factory()->employee()->create();

        LeaveRequest::factory()->count(3)->create(['user_id' => $employee->id]);

        $token = $admin->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/admin/leaves');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');
    }

    // ─────────────────────────────────────────────────────────────────
    // Employee can only see own leave data
    // ─────────────────────────────────────────────────────────────────

    public function test_employee_hanya_melihat_cuti_miliknya(): void
    {
        $emp1 = User::factory()->employee()->create();
        $emp2 = User::factory()->employee()->create();
        $token = $emp1->createToken('auth-token')->plainTextToken;

        LeaveRequest::factory()->count(2)->create(['user_id' => $emp1->id]);
        LeaveRequest::factory()->count(3)->create(['user_id' => $emp2->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/leaves');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_employee_tidak_dapat_melihat_cuti_milik_user_lain(): void
    {
        $emp1 = User::factory()->employee()->create();
        $emp2 = User::factory()->employee()->create();
        $leave = LeaveRequest::factory()->create(['user_id' => $emp2->id]);

        $token = $emp1->createToken('auth-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/leaves/{$leave->id}")
            ->assertStatus(404);
    }

    public function test_employee_dapat_melihat_detail_cutinya_sendiri(): void
    {
        $employee = User::factory()->employee()->create();
        $leave = LeaveRequest::factory()->create(['user_id' => $employee->id]);

        $token = $employee->createToken('auth-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/leaves/{$leave->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $leave->id);
    }
}
