<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for Chinchins Live
|--------------------------------------------------------------------------
*/

// Public Authentication & Registration Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public User Profile Route (view any profile by ID or 10-12 digit Account ID)
Route::get('/profile/{id}', [ProfileController::class, 'show']);

// Authenticated Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth status & logout
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile Management Endpoints
    Route::prefix('profile')->group(function () {
        Route::get('/me', [ProfileController::class, 'show']);
        Route::post('/update', [ProfileController::class, 'update']);
        Route::post('/upload-photos', [ProfileController::class, 'uploadPhotos']);
        Route::post('/upload-avatar', [ProfileController::class, 'uploadAvatar']);
        Route::post('/upload-cover', [ProfileController::class, 'uploadCover']);
        Route::post('/delete-photo', [ProfileController::class, 'deletePhoto']);
        Route::delete('/photos', [ProfileController::class, 'deletePhoto']);
        Route::post('/status', [ProfileController::class, 'toggleStatus']);
    });
});
