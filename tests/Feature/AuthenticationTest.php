<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    // ─────────────────────────────────────────────────────────────────
    // Registration
    // ─────────────────────────────────────────────────────────────────

    public function test_register_user_berhasil(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'New Employee',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Registration successful');

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    // ─────────────────────────────────────────────────────────────────
    // Login
    // ─────────────────────────────────────────────────────────────────

    public function test_login_email_password_berhasil(): void
    {
        User::factory()->employee()->create([
            'email' => 'emp@example.com',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'emp@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_login_gagal_jika_email_salah(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'notexist@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_login_gagal_jika_password_salah(): void
    {
        User::factory()->employee()->create(['email' => 'emp2@example.com']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'emp2@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    // ─────────────────────────────────────────────────────────────────
    // Logout
    // ─────────────────────────────────────────────────────────────────

    public function test_logout_berhasil(): void
    {
        $user = User::factory()->employee()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/auth/logout');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Logout successful');
    }

    // ─────────────────────────────────────────────────────────────────
    // Protected endpoints require token
    // ─────────────────────────────────────────────────────────────────

    public function test_endpoint_yang_membutuhkan_autentikasi_ditolak_tanpa_token(): void
    {
        $this->getJson('/api/leaves')->assertUnauthorized();
        $this->postJson('/api/auth/logout')->assertUnauthorized();
        $this->getJson('/api/admin/leaves')->assertUnauthorized();
    }
}
