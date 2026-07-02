<?php

namespace Tests\Feature;

/**
 * Menguji endpoint GET /api/auth/google/redirect → redirect ke Google.
 */
class GoogleOAuthRedirectTest extends GoogleOAuthTestCase
{
    /**
     * Endpoint GET /api/auth/google/redirect harus dapat diakses
     * dan mengembalikan redirect menuju Google OAuth.
     */
    public function test_google_redirect_mengembalikan_response_redirect(): void
    {
        $response = $this->getJson('/api/auth/google/redirect');

        // Socialite redirect mengembalikan 302 (redirect) atau 200 di mode stateless
        // Kita cukup pastikan endpoint dapat diakses (tidak 404/500)
        $response->assertStatus(302);
    }

    /**
     * URL redirect harus mengarah ke domain accounts.google.com.
     */
    public function test_google_redirect_menuju_url_google_oauth(): void
    {
        $response = $this->get('/api/auth/google/redirect');

        $response->assertRedirect();

        $location = $response->headers->get('Location');
        $this->assertStringContainsString('accounts.google.com', $location);
    }
}
