<?php

namespace Tests\Feature;

use App\Enums\LeaveStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Menguji bahwa file attachment benar-benar tersimpan di storage
 * dan path-nya tersimpan dengan benar di database.
 */
class LeaveAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        Storage::fake('public');
    }

    // ─────────────────────────────────────────────────────────────────
    // 3. File Upload — Storage & Database assertion
    // ─────────────────────────────────────────────────────────────────

    /**
     * Setelah pengajuan cuti berhasil, file attachment harus:
     *  - Benar-benar tersimpan di disk 'public' (Storage::assertExists)
     *  - Path-nya tersimpan di kolom `attachment` pada tabel leave_requests
     */
    public function test_attachment_tersimpan_di_storage_dan_database(): void
    {
        $employee = User::factory()->employee()->create();
        $token = $employee->createToken('auth-token')->plainTextToken;

        $file = UploadedFile::fake()->create('surat_cuti.pdf', 150, 'application/pdf');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/leaves', [
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
                'reason' => 'Medical check-up',
                'attachment' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        // Ambil path yang disimpan dari response
        $storedPath = $response->json('data.attachment');

        // Pastikan file benar-benar ada di disk 'public'
        Storage::disk('public')->assertExists($storedPath);

        // Pastikan database menyimpan path yang sama
        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $employee->id,
            'attachment' => $storedPath,
            'status' => LeaveStatus::Pending->value,
        ]);
    }

    /**
     * Memastikan attachment yang diupload menggunakan format gambar (jpg)
     * juga tersimpan dengan benar di storage dan database.
     */
    public function test_attachment_berupa_gambar_tersimpan_di_storage_dan_database(): void
    {
        $employee = User::factory()->employee()->create();
        $token = $employee->createToken('auth-token')->plainTextToken;

        $file = UploadedFile::fake()->image('bukti_cuti.jpg');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/leaves', [
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(7)->toDateString(),
                'reason' => 'Personal leave',
                'attachment' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $storedPath = $response->json('data.attachment');

        // File harus ada di storage 'public'
        Storage::disk('public')->assertExists($storedPath);

        // Path harus ada di database
        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $employee->id,
            'attachment' => $storedPath,
        ]);
    }

    /**
     * Path attachment yang tersimpan di database harus berawalan
     * dengan direktori 'attachments/' (sesuai implementasi controller).
     */
    public function test_path_attachment_dimulai_dengan_direktori_attachments(): void
    {
        $employee = User::factory()->employee()->create();
        $token = $employee->createToken('auth-token')->plainTextToken;

        $file = UploadedFile::fake()->create('dokumen.pdf', 50, 'application/pdf');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/leaves', [
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(4)->toDateString(),
                'reason' => 'Annual leave',
                'attachment' => $file,
            ]);

        $response->assertStatus(201);

        $storedPath = $response->json('data.attachment');

        // Pastikan path dimulai dengan folder 'attachments/'
        $this->assertStringStartsWith('attachments/', $storedPath);

        // Verifikasi file ada di storage
        Storage::disk('public')->assertExists($storedPath);
    }
}
