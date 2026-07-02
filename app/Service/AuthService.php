<?php

namespace App\Service;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\NewAccessToken;

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

}
