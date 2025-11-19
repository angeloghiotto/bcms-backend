<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Authentication
 * 
 * APIs for user authentication and registration
 */
class AuthController extends Controller
{
    /**
     * Register a new user
     * 
     * Register a new user account and receive an authentication token.
     * This endpoint accepts a JSON object with user registration data.
     * 
     * @bodyParam name string required The user's full name. Example: John Doe
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The user's password (minimum 8 characters). Example: password123
     * @bodyParam password_confirmation string required Password confirmation (must match password). Example: password123
     * 
     * @response 201 {
     *   "success": true,
     *   "message": "User registered successfully",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "created_at": "2024-01-01T00:00:00.000000Z"
     *     },
     *     "token": "1|xxxxxxxxxxxxx"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "email": ["The email has already been taken."],
     *     "password": ["The password confirmation does not match."]
     *   }
     * }
     * 
     * @param RegisterUserRequest $request
     * @return JsonResponse
     */
    public function register(RegisterUserRequest $request): JsonResponse
    {
        try {
            // Validation is automatically handled by RegisterUserRequest
            $validated = $request->validated();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Generate token for the user (using Sanctum)
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'created_at' => $user->created_at,
                    ],
                    'token' => $token,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user
     * 
     * Authenticate a user and receive an authentication token.
     * This endpoint accepts a JSON object with email and password.
     * 
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The user's password. Example: password123
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Login successful",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "created_at": "2024-01-01T00:00:00.000000Z"
     *     },
     *     "token": "1|xxxxxxxxxxxxx"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "The provided credentials are incorrect.",
     *   "errors": {
     *     "email": ["The provided credentials are incorrect."]
     *   }
     * }
     * 
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Validation is automatically handled by LoginRequest
            $credentials = $request->validated();

            // Attempt to authenticate the user
            if (!Auth::attempt($credentials)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            // Get the authenticated user
            /** @var User $user */
            $user = Auth::user();

            // Revoke all existing tokens (optional - for single device login)
            // $user->tokens()->delete();

            // Generate a new token
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'created_at' => $user->created_at,
                    ],
                    'token' => $token,
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

