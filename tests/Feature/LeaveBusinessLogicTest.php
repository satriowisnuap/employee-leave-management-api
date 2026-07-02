<?php

namespace Tests\Feature;

use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeaveBusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        Storage::fake('public');
    }

    // ─────────────────────────────────────────────────────────────────
    // Submit Leave — Happy path
    // ─────────────────────────────────────────────────────────────────

    public function test_pengajuan_cuti_berhasil_jika_kuota_tersedia(): void
    {
        $employee = User::factory()->employee()->create();
        $token = $employee->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/leaves', [
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
                'reason' => 'Family vacation',
                'attachment' => UploadedFile::fake()->create('leave.pdf', 100, 'application/pdf'),
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', LeaveStatus::Pending->value);
    }

    public function test_status_awal_selalu_pending(): void
    {
        $employee = User::factory()->employee()->create();
        $token = $employee->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/leaves', [
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'reason' => 'Rest',
                'attachment' => UploadedFile::fake()->create('file.jpg', 100, 'image/jpeg'),
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $employee->id,
            'status' => LeaveStatus::Pending->value,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // Submit Leave — Validation failures
    // ─────────────────────────────────────────────────────────────────

    public function test_pengajuan_gagal_jika_start_date_tidak_dikirim(): void
    {
        $employee = User::factory()->employee()->create();
        $token = $employee->createToken('auth-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/leaves', [
                'end_date' => now()->addDays(3)->toDateString(),
                'reason' => 'Holiday',
                'attachment' => UploadedFile::fake()->create('file.pdf', 100, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['start_date']);
    }

    public function test_pengajuan_gagal_jika_end_date_tidak_dikirim(): void
    {
        $employee = User::factory()->employee()->create();
        $token = $employee->createToken('auth-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/leaves', [
                'start_date' => now()->addDay()->toDateString(),
                'reason' => 'Holiday',
                'attachment' => UploadedFile::fake()->create('file.pdf', 100, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_pengajuan_gagal_jika_reason_tidak_dikirim(): void
    {
        $employee = User::factory()->employee()->create();
        $token = $employee->createToken('auth-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/leaves', [
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
                'attachment' => UploadedFile::fake()->create('file.pdf', 100, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reason']);
    }

    public function test_pengajuan_gagal_jika_attachment_tidak_dikirim(): void
    {
        $employee = User::factory()->employee()->create();
        $token = $employee->createToken('auth-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/leaves', [
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
                'reason' => 'Holiday',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['attachment']);
    }

    public function test_pengajuan_gagal_jika_attachment_tidak_valid(): void
    {
        $employee = User::factory()->employee()->create();
        $token = $employee->createToken('auth-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/leaves', [
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
                'reason' => 'Holiday',
                'attachment' => UploadedFile::fake()->create('file.exe', 100, 'application/octet-stream'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['attachment']);
    }

    // ─────────────────────────────────────────────────────────────────
    // Quota enforcement
    // ─────────────────────────────────────────────────────────────────

    public function test_pengajuan_gagal_jika_melebihi_kuota_12_hari(): void
    {
        $employee = User::factory()->employee()->create();
        $token = $employee->createToken('auth-token')->plainTextToken;

        // Seed 10 approved days in current year
        LeaveRequest::factory()->approved()->create([
            'user_id' => $employee->id,
            'start_date' => now()->startOfYear()->addDays(1)->toDateString(),
            'end_date' => now()->startOfYear()->addDays(10)->toDateString(),
            'days' => 10,
        ]);

        // Now request 5 more days — total would be 15 > 12
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/leaves', [
                'start_date' => now()->addDays(10)->toDateString(),
                'end_date' => now()->addDays(14)->toDateString(),
                'reason' => 'Vacation',
                'attachment' => UploadedFile::fake()->create('leave.pdf', 100, 'application/pdf'),
            ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    // ─────────────────────────────────────────────────────────────────
    // Approve & Reject
    // ─────────────────────────────────────────────────────────────────

    public function test_approve_berhasil_mengubah_status_menjadi_approved(): void
    {
        $admin = User::factory()->admin()->create();
        $employee = User::factory()->employee()->create();
        $leave = LeaveRequest::factory()->pending()->create([
            'user_id' => $employee->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'days' => 2,
        ]);
        $token = $admin->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/admin/leaves/{$leave->id}/approve");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', LeaveStatus::Approved->value);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => LeaveStatus::Approved->value,
            'approved_by' => $admin->id,
        ]);
    }

    public function test_reject_berhasil_mengubah_status_menjadi_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $employee = User::factory()->employee()->create();
        $leave = LeaveRequest::factory()->pending()->create([
            'user_id' => $employee->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'days' => 2,
        ]);
        $token = $admin->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/admin/leaves/{$leave->id}/reject");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', LeaveStatus::Rejected->value);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => LeaveStatus::Rejected->value,
            'approved_by' => $admin->id,
        ]);
    }

    public function test_pengajuan_yang_sudah_diapprove_tidak_dapat_diapprove_ulang(): void
    {
        $admin = User::factory()->admin()->create();
        $employee = User::factory()->employee()->create();
        $leave = LeaveRequest::factory()->approved()->create(['user_id' => $employee->id]);
        $token = $admin->createToken('auth-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/admin/leaves/{$leave->id}/approve")
            ->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_pengajuan_yang_sudah_direject_tidak_dapat_direject_ulang(): void
    {
        $admin = User::factory()->admin()->create();
        $employee = User::factory()->employee()->create();
        $leave = LeaveRequest::factory()->rejected()->create(['user_id' => $employee->id]);
        $token = $admin->createToken('auth-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/admin/leaves/{$leave->id}/reject")
            ->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_pengajuan_yang_sudah_diapprove_tidak_dapat_direject(): void
    {
        $admin = User::factory()->admin()->create();
        $employee = User::factory()->employee()->create();
        $leave = LeaveRequest::factory()->approved()->create(['user_id' => $employee->id]);
        $token = $admin->createToken('auth-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/admin/leaves/{$leave->id}/reject")
            ->assertStatus(400)
            ->assertJsonPath('success', false);
    }
}
