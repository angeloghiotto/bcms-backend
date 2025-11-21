<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostCategoryRequest;
use App\Http\Requests\UpdatePostCategoryRequest;
use App\Models\PostCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Post Categories Management
 *
 * APIs for managing post categories. Accessible by authenticated users.
 */
class PostCategoryController extends Controller
{
    /**
     * Get all post categories
     *
     * Retrieve a paginated list of all post categories, optionally filtered by client_id.
     *
     * @authenticated
     *
     * @queryParam page integer The page number. Example: 1
     * @queryParam per_page integer Number of items per page (default: 15). Example: 15
     * @queryParam client_id integer Filter categories by client ID. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": 1,
     *         "client_id": 1,
     *         "name": "Category Name",
     *         "created_at": "2024-01-01T00:00:00.000000Z",
     *         "updated_at": "2024-01-01T00:00:00.000000Z"
     *       }
     *     ],
     *     "per_page": 15,
     *     "total": 1
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = PostCategory::query();

            // Filter by client_id if provided
            if ($request->has('client_id')) {
                $query->where('client_id', $request->get('client_id'));
            }

            $categories = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $categories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new post category
     *
     * Create a new post category.
     *
     * @authenticated
     *
     * @bodyParam client_id integer required The ID of the client. Example: 1
     * @bodyParam name string required The category name. Example: Category Name
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Post category created successfully",
     *   "data": {
     *     "id": 1,
     *     "client_id": 1,
     *     "name": "Category Name",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "client_id": ["The client ID field is required."],
     *     "name": ["The name field is required."]
     *   }
     * }
     *
     * @param StorePostCategoryRequest $request
     * @return JsonResponse
     */
    public function store(StorePostCategoryRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $category = PostCategory::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Post category created successfully',
                'data' => $category,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific post category
     *
     * Retrieve details of a specific post category by ID.
     *
     * @authenticated
     *
     * @urlParam id integer required The ID of the category. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "client_id": 1,
     *     "name": "Category Name",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Post category not found"
     * }
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $category = PostCategory::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $category,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Post category not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a post category
     *
     * Update an existing post category.
     *
     * @authenticated
     *
     * @urlParam id integer required The ID of the category. Example: 1
     * @bodyParam client_id integer required The ID of the client. Example: 1
     * @bodyParam name string sometimes The category name. Example: Updated Category Name
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Post category updated successfully",
     *   "data": {
     *     "id": 1,
     *     "client_id": 1,
     *     "name": "Updated Category Name",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Post category not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "client_id": ["The client ID field is required."]
     *   }
     * }
     *
     * @param UpdatePostCategoryRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdatePostCategoryRequest $request, int $id): JsonResponse
    {
        try {
            $category = PostCategory::findOrFail($id);
            $validated = $request->validated();
            $category->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Post category updated successfully',
                'data' => $category->fresh(),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Post category not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a post category
     *
     * Delete a post category record.
     *
     * @authenticated
     *
     * @urlParam id integer required The ID of the category. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Post category deleted successfully"
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Post category not found"
     * }
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $category = PostCategory::findOrFail($id);
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post category deleted successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Post category not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

