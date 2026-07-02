<?php

namespace App\Service;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Socialite\Facades\Socialite;

class AuthService
{
    public function login(array $credentials): array
    {
        if (! Auth::attempt($credentials)) {
            throw new \Exception('Invalid credentials.');
        }

        /** @var User $user */
        $user = Auth::user();

        /** @var NewAccessToken $token */
        $token = $user->createToken('auth-token');

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function handleProviderCallback(string $provider): array
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            throw new \Exception('Failed to authenticate with '.$provider);
        }

        /** @var User $user */
        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name' => $socialUser->getName(),
                'provider_name' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'password' => null,
            ]
        );

        if (! $user->hasAnyRole(['Employee', 'Admin'])) {
            $user->assignRole('Employee');
        }

        $token = $user->createToken('auth-token');

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
    }
}
