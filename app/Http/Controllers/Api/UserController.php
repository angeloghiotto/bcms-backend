<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @group Users Management
 *
 * APIs for managing users. Only accessible by admin users.
 */
class UserController extends Controller
{
    /**
     * Get all users
     *
     * Retrieve a paginated list of all users.
     *
     * @authenticated
     *
     * @queryParam page integer The page number. Example: 1
     * @queryParam per_page integer Number of items per page (default: 15). Example: 15
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": 1,
     *         "name": "John Doe",
     *         "email": "john@example.com",
     *         "admin": false,
     *         "created_at": "2024-01-01T00:00:00.000000Z",
     *         "updated_at": "2024-01-01T00:00:00.000000Z"
     *       }
     *     ],
     *     "per_page": 15,
     *     "total": 1
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Unauthorized. Admin access required."
     * }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $users = User::select('id', 'name', 'email', 'admin', 'created_at', 'updated_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users,
        ], 200);
    }

    /**
     * Create a new user
     *
     * Create a new user account. Admin can set admin status.
     *
     * @authenticated
     *
     * @bodyParam name string required The user's full name. Example: John Doe
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The user's password (minimum 8 characters). Example: password123
     * @bodyParam password_confirmation string required Password confirmation (must match password). Example: password123
     * @bodyParam admin boolean Whether the user should be an admin. Example: false
     *
     * @response 201 {
     *   "success": true,
     *   "message": "User created successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "admin": false,
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Unauthorized. Admin access required."
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
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'admin' => $validated['admin'] ?? false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'admin' => $user->admin,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific user
     *
     * Retrieve details of a specific user by ID.
     *
     * @authenticated
     *
     * @urlParam id integer required The ID of the user. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "admin": false,
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Unauthorized. Admin access required."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "User not found"
     * }
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = User::select('id', 'name', 'email', 'admin', 'created_at', 'updated_at')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $user,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a user
     *
     * Update an existing user record.
     *
     * @authenticated
     *
     * @urlParam id integer required The ID of the user. Example: 1
     * @bodyParam name string sometimes The user's full name. Example: John Doe
     * @bodyParam email string sometimes The user's email address. Example: john@example.com
     * @bodyParam password string sometimes The user's new password (minimum 8 characters). Example: newpassword123
     * @bodyParam password_confirmation string sometimes Password confirmation (required if password is provided). Example: newpassword123
     * @bodyParam admin boolean Whether the user should be an admin. Example: false
     *
     * @response 200 {
     *   "success": true,
     *   "message": "User updated successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe Updated",
     *     "email": "john@example.com",
     *     "admin": true,
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Unauthorized. Admin access required."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "User not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "email": ["The email has already been taken."]
     *   }
     * }
     *
     * @param UpdateUserRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $validated = $request->validated();

            // Update password if provided
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            // Remove password_confirmation from validated data
            unset($validated['password_confirmation']);

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'admin' => $user->admin,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a user
     *
     * Delete a user record.
     *
     * @authenticated
     *
     * @urlParam id integer required The ID of the user. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "User deleted successfully"
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Unauthorized. Admin access required."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "User not found"
     * }
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search users by email
     *
     * Search for users by email address. Useful for AJAX autocomplete/search functionality.
     *
     * @authenticated
     *
     * @queryParam email string required The email address to search for (partial matches supported). Example: john@
     * @queryParam limit integer Maximum number of results to return (default: 10). Example: 10
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "admin": false
     *     },
     *     {
     *       "id": 2,
     *       "name": "Jane Smith",
     *       "email": "jane@example.com",
     *       "admin": false
     *     }
     *   ]
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Unauthorized. Admin access required."
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "email": ["The email field is required."]
     *   }
     * }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchByEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'min:1'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        try {
            $email = $request->get('email');
            $limit = $request->get('limit', 10);

            $users = User::select('id', 'name', 'email', 'admin')
                ->where('email', 'like', "%{$email}%")
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $users,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search users',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

