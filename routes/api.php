<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\AdminLeaveController;

/*
|--------------------------------------------------------------------------
| Authentication Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    Route::get('/google/redirect', [AuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);

    // Protected
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Current user info
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles');
    });

    /*
    |----------------------------------------------------------------------
    | Employee Routes
    |----------------------------------------------------------------------
    */
    Route::middleware('role:Employee|Admin')->group(function () {
        Route::get('/leaves',      [LeaveController::class, 'index']);
        Route::get('/leaves/{id}', [LeaveController::class, 'show']);
    });

    Route::middleware('role:Employee')->group(function () {
        Route::post('/leaves', [LeaveController::class, 'store']);
    });

    /*
    |----------------------------------------------------------------------
    | Admin Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('admin')->middleware('role:Admin')->group(function () {
        Route::get('/leaves',                    [AdminLeaveController::class, 'index']);
        Route::patch('/leaves/{id}/approve',     [AdminLeaveController::class, 'approve']);
        Route::patch('/leaves/{id}/reject',      [AdminLeaveController::class, 'reject']);
    });
});
