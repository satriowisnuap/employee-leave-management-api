<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\LoginRequest;
use App\Service\AuthService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function login(LoginRequest $request)
    {
        try {
            $data = $this->authService->login($request->validated());
            return ApiResponse::success($data, 'Login successful');
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 401);
        }
    }

    public function register(\App\Http\Requests\RegisterRequest $request)
    {
        try {
            $user = \App\Models\User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            ]);
            
            $user->assignRole('Employee');

            return ApiResponse::success($user, 'Registration successful', 201);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return ApiResponse::success(null, 'Logout successful');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $data = $this->authService->handleProviderCallback('google');
            return ApiResponse::success($data, 'Google Login successful');
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 401);
        }
    }
}
