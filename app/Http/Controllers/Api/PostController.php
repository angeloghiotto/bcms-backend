<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Posts Management
 *
 * APIs for managing posts. Accessible by authenticated users.
 */
class PostController extends Controller
{
    /**
     * Get all posts
     *
     * Retrieve a paginated list of all posts.
     *
     * @authenticated
     *
     * @queryParam page integer The page number. Example: 1
     * @queryParam per_page integer Number of items per page (default: 15). Example: 15
     * @queryParam client_id integer Filter posts by client ID. Example: 1
     * @queryParam user_id integer Filter posts by user ID. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": 1,
     *         "user_id": 1,
     *         "client_id": 1,
     *         "post_category_id": 1,
     *         "title": "Sample Post",
     *         "content": "This is the post content",
     *         "image_url": "https://example.com/image.jpg",
     *         "created_at": "2024-01-01T00:00:00.000000Z",
     *         "updated_at": "2024-01-01T00:00:00.000000Z",
     *         "user": {
     *           "id": 1,
     *           "name": "John Doe",
     *           "email": "john@example.com"
     *         },
     *         "client": {
     *           "id": 1,
     *           "name": "Acme Corporation",
     *           "website": "https://acme.com"
     *         },
     *         "category": {
     *           "id": 1,
     *           "name": "Category Name"
     *         }
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
        $perPage = $request->get('per_page', 15);
        $query = Post::with(['user', 'client', 'category']);

        // Filter by client_id if provided
        if ($request->has('client_id')) {
            $query->where('client_id', $request->get('client_id'));
        }

        // Filter by user_id if provided
        if ($request->has('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        $posts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $posts,
        ], 200);
    }

    /**
     * Create a new post
     *
     * Create a new post record.
     *
     * @authenticated
     *
     * @bodyParam user_id integer required The ID of the user creating the post. Example: 1
     * @bodyParam client_id integer required The ID of the client. Example: 1
     * @bodyParam post_category_id integer required The ID of the post category. Example: 1
     * @bodyParam title string required The post title. Example: Sample Post
     * @bodyParam content string required The post content. Example: This is the post content
     * @bodyParam image_url string nullable The URL of the post image. Example: https://example.com/image.jpg
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Post created successfully",
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "client_id": 1,
     *     "post_category_id": 1,
     *     "title": "Sample Post",
     *     "content": "This is the post content",
     *     "image_url": "https://example.com/image.jpg",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z",
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     },
     *     "client": {
     *       "id": 1,
     *       "name": "Acme Corporation"
     *     },
     *     "category": {
     *       "id": 1,
     *       "name": "Category Name"
     *     }
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "title": ["The title field is required."],
     *     "client_id": ["The selected client does not exist."]
     *   }
     * }
     *
     * @param StorePostRequest $request
     * @return JsonResponse
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $post = Post::create($validated);
            $post->load(['user', 'client', 'category']);

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $post,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific post
     *
     * Retrieve details of a specific post by ID.
     *
     * @authenticated
     *
     * @urlParam id integer required The ID of the post. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "client_id": 1,
     *     "post_category_id": 1,
     *     "title": "Sample Post",
     *     "content": "This is the post content",
     *     "image_url": "https://example.com/image.jpg",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z",
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     },
     *     "client": {
     *       "id": 1,
     *       "name": "Acme Corporation"
     *     },
     *     "category": {
     *       "id": 1,
     *       "name": "Category Name"
     *     }
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Post not found"
     * }
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $post = Post::with(['user', 'client', 'category'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $post,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a post
     *
     * Update an existing post record.
     *
     * @authenticated
     *
     * @urlParam id integer required The ID of the post. Example: 1
     * @bodyParam user_id integer sometimes The ID of the user creating the post. Example: 1
     * @bodyParam client_id integer required The ID of the client. Example: 1
     * @bodyParam post_category_id integer sometimes The ID of the post category. Example: 1
     * @bodyParam title string sometimes The post title. Example: Sample Post Updated
     * @bodyParam content string sometimes The post content. Example: This is the updated post content
     * @bodyParam image_url string nullable The URL of the post image. Example: https://example.com/image.jpg
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Post updated successfully",
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "client_id": 1,
     *     "post_category_id": 1,
     *     "title": "Sample Post Updated",
     *     "content": "This is the updated post content",
     *     "image_url": "https://example.com/image.jpg",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z",
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     },
     *     "client": {
     *       "id": 1,
     *       "name": "Acme Corporation"
     *     },
     *     "category": {
     *       "id": 1,
     *       "name": "Category Name"
     *     }
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Post not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "client_id": ["The selected client does not exist."]
     *   }
     * }
     *
     * @param UpdatePostRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdatePostRequest $request, int $id): JsonResponse
    {
        try {
            $post = Post::findOrFail($id);
            $validated = $request->validated();
            $post->update($validated);
            $post->load(['user', 'client', 'category']);

            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully',
                'data' => $post,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a post
     *
     * Delete a post record.
     *
     * @authenticated
     *
     * @urlParam id integer required The ID of the post. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Post deleted successfully"
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Post not found"
     * }
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $post = Post::findOrFail($id);
            $post->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

