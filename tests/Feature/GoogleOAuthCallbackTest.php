<?php

namespace Tests\Feature;

use App\Models\User;

/**
 * Menguji endpoint GET /api/auth/google/callback → proses login dengan Socialite mock.
 */
class GoogleOAuthCallbackTest extends GoogleOAuthTestCase
{
    /**
     * Callback membuat user baru jika belum ada di database,
     * meng-assign role Employee, dan mengembalikan Sanctum token.
     */
    public function test_callback_membuat_user_baru_jika_belum_ada(): void
    {
        $socialiteUser = $this->mockSocialiteUser(
            email: 'newgoogle@example.com',
            name: 'New Google User',
            id: 'google-uid-new-001',
            avatar: 'https://lh3.googleusercontent.com/new.jpg'
        );
        $this->fakeSocialite($socialiteUser);

        $response = $this->getJson('/api/auth/google/callback');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Google Login successful')
            ->assertJsonStructure([
                'data' => ['token', 'user'],
            ]);

        // User harus ada di database
        $this->assertDatabaseHas('users', [
            'email' => 'newgoogle@example.com',
            'name' => 'New Google User',
            'provider_name' => 'google',
            'provider_id' => 'google-uid-new-001',
        ]);
    }

    /**
     * Callback meng-login user yang sudah ada (tidak membuat duplikat)
     * dan tetap mengembalikan Sanctum token.
     */
    public function test_callback_login_user_yang_sudah_ada(): void
    {
        // Buat user yang sudah ada sebelumnya
        $existingUser = User::factory()->employee()->create([
            'email' => 'existing.google@example.com',
            'provider_name' => 'google',
            'provider_id' => 'google-uid-existing-001',
        ]);

        $socialiteUser = $this->mockSocialiteUser(
            email: 'existing.google@example.com',
            name: $existingUser->name,
            id: 'google-uid-existing-001',
            avatar: 'https://lh3.googleusercontent.com/existing.jpg'
        );
        $this->fakeSocialite($socialiteUser);

        $response = $this->getJson('/api/auth/google/callback');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['token', 'user'],
            ]);

        // Tidak boleh ada duplikat user
        $this->assertDatabaseCount('users', 1);
    }

    /**
     * Callback harus membuat Sanctum token untuk user yang berhasil login via Google.
     */
    public function test_callback_membuat_sanctum_token(): void
    {
        $socialiteUser = $this->mockSocialiteUser(
            email: 'tokentest.google@example.com',
            id: 'google-uid-token-001'
        );
        $this->fakeSocialite($socialiteUser);

        $response = $this->getJson('/api/auth/google/callback');

        $response->assertOk();

        $token = $response->json('data.token');

        // Token harus berupa string yang tidak kosong
        $this->assertNotNull($token);
        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        // Pastikan personal_access_tokens memiliki record untuk user ini
        $user = User::where('email', 'tokentest.google@example.com')->first();
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    /**
     * User yang dibuat melalui Google OAuth harus mendapatkan role Employee.
     */
    public function test_callback_user_baru_mendapatkan_role_employee(): void
    {
        $socialiteUser = $this->mockSocialiteUser(
            email: 'roleccheck.google@example.com',
            id: 'google-uid-role-001'
        );
        $this->fakeSocialite($socialiteUser);

        $this->getJson('/api/auth/google/callback')->assertOk();

        $user = User::where('email', 'roleccheck.google@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Employee'));
    }

    /**
     * Response callback harus mengikuti format API standar dengan field
     * success, message, dan data (yang berisi user dan token).
     */
    public function test_callback_response_sesuai_format_api(): void
    {
        $socialiteUser = $this->mockSocialiteUser(
            email: 'formattest.google@example.com',
            id: 'google-uid-format-001'
        );
        $this->fakeSocialite($socialiteUser);

        $response = $this->getJson('/api/auth/google/callback');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ]);
    }
}
