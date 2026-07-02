<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji bahwa GET /api/leaves mengembalikan data berdasarkan latest()
 * (dari yang paling baru ke paling lama).
 */
class LeaveSortingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    // ─────────────────────────────────────────────────────────────────
    // 5. Sorting — GET /api/leaves diurutkan dari terbaru (latest())
    // ─────────────────────────────────────────────────────────────────

    /**
     * GET /api/leaves harus mengembalikan data diurutkan dari created_at terbaru.
     * Buat beberapa LeaveRequest dengan created_at yang berbeda, lalu pastikan
     * urutan response sesuai urutan terbaru → terlama.
     */
    public function test_daftar_cuti_diurutkan_dari_terbaru_ke_terlama(): void
    {
        $employee = User::factory()->employee()->create();
        $token    = $employee->createToken('auth-token')->plainTextToken;

        // Buat 3 record dengan created_at berbeda (urutan pembuatan: lama → baru)
        $oldest = LeaveRequest::factory()->create([
            'user_id'    => $employee->id,
            'created_at' => Carbon::now()->subDays(10),
            'start_date' => now()->addDays(20)->toDateString(),
            'end_date'   => now()->addDays(21)->toDateString(),
        ]);

        $middle = LeaveRequest::factory()->create([
            'user_id'    => $employee->id,
            'created_at' => Carbon::now()->subDays(5),
            'start_date' => now()->addDays(25)->toDateString(),
            'end_date'   => now()->addDays(26)->toDateString(),
        ]);

        $newest = LeaveRequest::factory()->create([
            'user_id'    => $employee->id,
            'created_at' => Carbon::now()->subDay(),
            'start_date' => now()->addDays(30)->toDateString(),
            'end_date'   => now()->addDays(31)->toDateString(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->getJson('/api/leaves');

        $response->assertOk()
                 ->assertJsonPath('success', true);

        $data = $response->json('data');

        $this->assertCount(3, $data);

        // Index 0 = terbaru, index 1 = tengah, index 2 = terlama
        $this->assertEquals($newest->id,  $data[0]['id']);
        $this->assertEquals($middle->id,  $data[1]['id']);
        $this->assertEquals($oldest->id,  $data[2]['id']);
    }

    /**
     * Memastikan urutan tetap konsisten meskipun record dibuat
     * dengan selisih waktu yang sangat kecil (detik).
     */
    public function test_urutan_cuti_konsisten_dengan_selisih_waktu_detik(): void
    {
        $employee = User::factory()->employee()->create();
        $token    = $employee->createToken('auth-token')->plainTextToken;

        $first = LeaveRequest::factory()->create([
            'user_id'    => $employee->id,
            'created_at' => Carbon::now()->subSeconds(30),
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date'   => now()->addDays(6)->toDateString(),
        ]);

        $second = LeaveRequest::factory()->create([
            'user_id'    => $employee->id,
            'created_at' => Carbon::now()->subSeconds(10),
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date'   => now()->addDays(11)->toDateString(),
        ]);

        $third = LeaveRequest::factory()->create([
            'user_id'    => $employee->id,
            'created_at' => Carbon::now(),
            'start_date' => now()->addDays(15)->toDateString(),
            'end_date'   => now()->addDays(16)->toDateString(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->getJson('/api/leaves');

        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->toArray();

        // Harus berurutan: terbaru dulu
        $this->assertEquals([$third->id, $second->id, $first->id], $ids);
    }

    /**
     * Memastikan data dari user lain tidak ikut masuk, dan data milik user saat ini
     * tetap terurut dari terbaru ke terlama.
     */
    public function test_urutan_cuti_tidak_terpengaruh_oleh_cuti_user_lain(): void
    {
        $employee1 = User::factory()->employee()->create();
        $employee2 = User::factory()->employee()->create();
        $token     = $employee1->createToken('auth-token')->plainTextToken;

        // Buat cuti employee2 (lebih baru) — tidak boleh muncul di response employee1
        LeaveRequest::factory()->create([
            'user_id'    => $employee2->id,
            'created_at' => Carbon::now(),
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date'   => now()->addDays(2)->toDateString(),
        ]);

        // Buat 2 cuti milik employee1 dengan created_at berbeda
        $older = LeaveRequest::factory()->create([
            'user_id'    => $employee1->id,
            'created_at' => Carbon::now()->subDays(3),
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date'   => now()->addDays(6)->toDateString(),
        ]);

        $newer = LeaveRequest::factory()->create([
            'user_id'    => $employee1->id,
            'created_at' => Carbon::now()->subDay(),
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date'   => now()->addDays(11)->toDateString(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->getJson('/api/leaves');

        $response->assertOk();

        $data = $response->json('data');

        // Hanya 2 milik employee1
        $this->assertCount(2, $data);

        // Terbaru dulu
        $this->assertEquals($newer->id, $data[0]['id']);
        $this->assertEquals($older->id, $data[1]['id']);
    }
}
