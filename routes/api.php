<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Admin-only routes (require authentication and admin role)
Route::middleware(['auth:sanctum', 'admin'])->prefix('clients')->group(function () {
    Route::get('/', [ClientController::class, 'index']);
    Route::post('/', [ClientController::class, 'store']);
    
    // User association routes (must be before /{id} route)
    Route::get('/{id}/users', [ClientController::class, 'users']);
    Route::post('/{clientId}/users/{userId}', [ClientController::class, 'attachUser']);
    Route::delete('/{clientId}/users/{userId}', [ClientController::class, 'detachUser']);
    
    Route::get('/{id}', [ClientController::class, 'show']);
    Route::put('/{id}', [ClientController::class, 'update']);
    Route::delete('/{id}', [ClientController::class, 'destroy']);
});

// Admin-only routes for users management
Route::middleware(['auth:sanctum', 'admin'])->prefix('users')->group(function () {
    Route::get('/search', [UserController::class, 'searchByEmail']);
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

