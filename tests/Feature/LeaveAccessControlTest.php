<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji kontrol akses pada endpoint POST /api/leaves:
 *  1. Tanpa token  → 401 Unauthorized
 *  2. Login sebagai Admin → 403 Forbidden (endpoint khusus Employee)
 */
class LeaveAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    // ─────────────────────────────────────────────────────────────────
    // 1. Authentication — 401 Unauthorized
    // ─────────────────────────────────────────────────────────────────

    /**
     * Endpoint POST /api/leaves tidak boleh dapat diakses tanpa token.
     */
    public function test_post_leaves_mengembalikan_401_tanpa_autentikasi(): void
    {
        $response = $this->postJson('/api/leaves', [
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'reason' => 'Family vacation',
        ]);

        $response->assertStatus(401);
    }

    // ─────────────────────────────────────────────────────────────────
    // 2. Authorization — 403 Forbidden (Admin mencoba endpoint Employee)
    // ─────────────────────────────────────────────────────────────────

    /**
     * User dengan role Admin tidak dapat menggunakan POST /api/leaves
     * karena endpoint tersebut hanya diperuntukkan bagi Employee.
     */
    public function test_post_leaves_mengembalikan_403_jika_login_sebagai_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/leaves', [
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
                'reason' => 'Family vacation',
            ]);

        $response->assertStatus(403);
    }
}
