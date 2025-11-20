<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Clients Management
 *
 * APIs for managing clients. Only accessible by admin users.
 */
class ClientController extends Controller
{
    /**
     * Get all clients
     *
     * Retrieve a paginated list of all clients.
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
     *         "name": "Acme Corporation",
     *         "email": "contact@acme.com",
     *         "phone": "+1234567890",
     *         "website": "https://acme.com",
     *         "address": "123 Main St",
     *         "city": "New York",
     *         "state": "NY",
     *         "country": "USA",
     *         "postal_code": "10001",
     *         "notes": "Important client",
     *         "created_at": "2024-01-01T00:00:00.000000Z",
     *         "updated_at": "2024-01-01T00:00:00.000000Z"
     *       }
     *     ],
     *     "first_page_url": "http://localhost/api/clients?page=1",
     *     "from": 1,
     *     "last_page": 1,
     *     "last_page_url": "http://localhost/api/clients?page=1",
     *     "links": [
     *       {
     *         "url": null,
     *         "label": "&laquo; Previous",
     *         "active": false
     *       },
     *       {
     *         "url": "http://localhost/api/clients?page=1",
     *         "label": "1",
     *         "active": true
     *       },
     *       {
     *         "url": null,
     *         "label": "Next &raquo;",
     *         "active": false
     *       }
     *     ],
     *     "next_page_url": null,
     *     "path": "http://localhost/api/clients",
     *     "per_page": 15,
     *     "prev_page_url": null,
     *     "to": 1,
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
        $clients = Client::paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $clients,
        ], 200);
    }

    /**
     * Create a new client
     *
     * Create a new client record.
     *
     * @authenticated
     *
     * @bodyParam name string required The client's name. Example: Acme Corporation
     * @bodyParam email string nullable The client's email address. Example: contact@acme.com
     * @bodyParam phone string nullable The client's phone number. Example: +1234567890
     * @bodyParam website string required The client's website URL. Example: https://acme.com
     * @bodyParam address string nullable The client's street address. Example: 123 Main St
     * @bodyParam city string nullable The client's city. Example: New York
     * @bodyParam state string nullable The client's state/province. Example: NY
     * @bodyParam country string nullable The client's country. Example: USA
     * @bodyParam postal_code string nullable The client's postal/ZIP code. Example: 10001
     * @bodyParam notes string nullable Additional notes about the client. Example: Important client
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Client created successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Acme Corporation",
     *     "email": "contact@acme.com",
     *     "phone": "+1234567890",
     *     "website": "https://acme.com",
     *     "address": "123 Main St",
     *     "city": "New York",
     *     "state": "NY",
     *     "country": "USA",
     *     "postal_code": "10001",
     *     "notes": "Important client",
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
     *     "name": ["The name field is required."],
     *     "website": ["The website must be a valid URL."]
     *   }
     * }
     *
     * @param StoreClientRequest $request
     * @return JsonResponse
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $client = Client::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Client created successfully',
                'data' => $client,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create client',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific client
     *
     * Retrieve details of a specific client by ID.
     *
     * @authenticated
     *
     * @urlParam id integer required The ID of the client. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "name": "Acme Corporation",
     *     "email": "contact@acme.com",
     *     "phone": "+1234567890",
     *     "website": "https://acme.com",
     *     "address": "123 Main St",
     *     "city": "New York",
     *     "state": "NY",
     *     "country": "USA",
     *     "postal_code": "10001",
     *     "notes": "Important client",
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
     *   "message": "Client not found"
     * }
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $client = Client::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $client,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve client',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a client
     *
     * Update an existing client record.
     *
     * @authenticated
     *
     * @urlParam id integer required The ID of the client. Example: 1
     * @bodyParam name string sometimes The client's name. Example: Acme Corporation
     * @bodyParam email string nullable The client's email address. Example: contact@acme.com
     * @bodyParam phone string nullable The client's phone number. Example: +1234567890
     * @bodyParam website string sometimes The client's website URL. Example: https://acme.com
     * @bodyParam address string nullable The client's street address. Example: 123 Main St
     * @bodyParam city string nullable The client's city. Example: New York
     * @bodyParam state string nullable The client's state/province. Example: NY
     * @bodyParam country string nullable The client's country. Example: USA
     * @bodyParam postal_code string nullable The client's postal/ZIP code. Example: 10001
     * @bodyParam notes string nullable Additional notes about the client. Example: Important client
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Client updated successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Acme Corporation Updated",
     *     "email": "contact@acme.com",
     *     "phone": "+1234567890",
     *     "website": "https://acme.com",
     *     "address": "123 Main St",
     *     "city": "New York",
     *     "state": "NY",
     *     "country": "USA",
     *     "postal_code": "10001",
     *     "notes": "Important client",
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
     *   "message": "Client not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "website": ["The website must be a valid URL."]
     *   }
     * }
     *
     * @param UpdateClientRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateClientRequest $request, int $id): JsonResponse
    {
        try {
            $client = Client::findOrFail($id);
            $validated = $request->validated();
            $client->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Client updated successfully',
                'data' => $client->fresh(),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update client',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a client
     *
     * Delete a client record.
     *
     * @authenticated
     *
     * @urlParam id integer required The ID of the client. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Client deleted successfully"
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Unauthorized. Admin access required."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Client not found"
     * }
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $client = Client::findOrFail($id);
            $client->delete();

            return response()->json([
                'success' => true,
                'message' => 'Client deleted successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete client',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Associate a user to a client
     *
     * Attach a user to a client, creating the association in the pivot table.
     *
     * @authenticated
     *
     * @urlParam clientId integer required The ID of the client. Example: 1
     * @urlParam userId integer required The ID of the user. Example: 2
     *
     * @response 200 {
     *   "success": true,
     *   "message": "User associated with client successfully",
     *   "data": {
     *     "client": {
     *       "id": 1,
     *       "name": "Acme Corporation",
     *       "website": "https://acme.com"
     *     },
     *     "user": {
     *       "id": 2,
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     }
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Unauthorized. Admin access required."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Client or user not found"
     * }
     * @response 409 {
     *   "success": false,
     *   "message": "User is already associated with this client"
     * }
     *
     * @param int $clientId
     * @param int $userId
     * @return JsonResponse
     */
    public function attachUser(int $clientId, int $userId): JsonResponse
    {
        try {
            $client = Client::findOrFail($clientId);
            $user = User::findOrFail($userId);

            // Check if user is already associated
            if ($client->users()->where('users.id', $userId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already associated with this client',
                ], 409);
            }

            // Attach the user to the client
            $client->users()->attach($userId);

            return response()->json([
                'success' => true,
                'message' => 'User associated with client successfully',
                'data' => [
                    'client' => [
                        'id' => $client->id,
                        'name' => $client->name,
                        'website' => $client->website,
                    ],
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Client or user not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to associate user with client',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dissociate a user from a client
     *
     * Remove the association between a user and a client.
     *
     * @authenticated
     *
     * @urlParam clientId integer required The ID of the client. Example: 1
     * @urlParam userId integer required The ID of the user. Example: 2
     *
     * @response 200 {
     *   "success": true,
     *   "message": "User dissociated from client successfully",
     *   "data": {
     *     "client": {
     *       "id": 1,
     *       "name": "Acme Corporation",
     *       "website": "https://acme.com"
     *     },
     *     "user": {
     *       "id": 2,
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     }
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Unauthorized. Admin access required."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Client or user not found, or association does not exist"
     * }
     *
     * @param int $clientId
     * @param int $userId
     * @return JsonResponse
     */
    public function detachUser(int $clientId, int $userId): JsonResponse
    {
        try {
            $client = Client::findOrFail($clientId);
            $user = User::findOrFail($userId);

            // Check if user is associated
            if (!$client->users()->where('users.id', $userId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not associated with this client',
                ], 404);
            }

            // Detach the user from the client
            $client->users()->detach($userId);

            return response()->json([
                'success' => true,
                'message' => 'User dissociated from client successfully',
                'data' => [
                    'client' => [
                        'id' => $client->id,
                        'name' => $client->name,
                        'website' => $client->website,
                    ],
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Client or user not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to dissociate user from client',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

