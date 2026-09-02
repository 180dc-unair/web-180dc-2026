<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService,
    ) {
        //
    }

    public function index(Request $request): JsonResponse
    {
        $isAdmin = $request->user()?->role === 'admin';

        $filters = $request->only(['search', 'type', 'is_featured', 'sort', 'direction', 'per_page', 'page']);

        $paginator = $this->clientService->paginate($filters, $isAdmin);

        return response()->json([
            'status' => 'success',
            'message' => 'Clients retrieved successfully.',
            'data' => ClientResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $client = $this->clientService->findBySlug($slug);

        if (! $client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Client retrieved successfully.',
            'data' => new ClientResource($client),
        ]);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->clientService->createClient($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Client created successfully.',
            'data' => new ClientResource($client),
        ], 201);
    }

    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $updatedClient = $this->clientService->updateClient($client, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Client updated successfully.',
            'data' => new ClientResource($updatedClient),
        ]);
    }

    public function destroy(Client $client): JsonResponse
    {
        $this->clientService->deleteClient($client);

        return response()->json([
            'status' => 'success',
            'message' => 'Client deleted successfully.',
            'data' => null,
        ]);
    }
}
