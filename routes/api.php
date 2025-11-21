<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\PostCategoryController;
use App\Http\Controllers\Api\PostController;
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

// Posts management routes (require authentication)
Route::middleware('auth:sanctum')->prefix('posts')->group(function () {
    Route::get('/', [PostController::class, 'index']);
    Route::post('/', [PostController::class, 'store']);
    Route::get('/{id}', [PostController::class, 'show']);
    Route::post('/{id}', [PostController::class, 'update']);
    Route::delete('/{id}', [PostController::class, 'destroy']);
});

// User's own clients route (require authentication)
Route::middleware('auth:sanctum')->get('/my-clients', [ClientController::class, 'myClients']);

// Post categories management routes (require authentication)
Route::middleware('auth:sanctum')->prefix('post-categories')->group(function () {
    Route::get('/', [PostCategoryController::class, 'index']);
    Route::post('/', [PostCategoryController::class, 'store']);
    Route::get('/{id}', [PostCategoryController::class, 'show']);
    Route::put('/{id}', [PostCategoryController::class, 'update']);
    Route::delete('/{id}', [PostCategoryController::class, 'destroy']);
});

