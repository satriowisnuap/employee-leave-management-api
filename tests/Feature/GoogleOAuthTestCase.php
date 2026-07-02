<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Tests\TestCase;

/**
 * Base class untuk test Google OAuth.
 * Berisi setup umum & helper mock Socialite yang dipakai
 * oleh GoogleOAuthRedirectTest dan GoogleOAuthCallbackTest.
 */
abstract class GoogleOAuthTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    /**
     * Helper: buat mock SocialiteUser yang akan digunakan pada setiap test callback.
     */
    protected function mockSocialiteUser(
        string $email = 'google.user@example.com',
        string $name = 'Google User',
        string $id = 'google-provider-id-123',
        string $avatar = 'https://lh3.googleusercontent.com/photo.jpg'
    ): SocialiteUser {
        $socialiteUser = $this->createMock(SocialiteUser::class);

        $socialiteUser->method('getEmail')->willReturn($email);
        $socialiteUser->method('getName')->willReturn($name);
        $socialiteUser->method('getId')->willReturn($id);
        $socialiteUser->method('getAvatar')->willReturn($avatar);

        return $socialiteUser;
    }

    /**
     * Helper: daftarkan Socialite mock ke container Laravel.
     */
    protected function fakeSocialite(SocialiteUser $socialiteUser): void
    {
        $provider = $this->createMock(GoogleProvider::class);
        $provider->method('stateless')->willReturnSelf();
        $provider->method('user')->willReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);
    }
}
