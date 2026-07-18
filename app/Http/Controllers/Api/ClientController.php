<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService,
    ) {
        //
    }

    public function index(): JsonResponse
    {
        $clients = $this->clientService->getClients();

        return response()->json([
            'status' => 'success',
            'message' => 'Clients retrieved successfully.',
            'data' => ClientResource::collection($clients),
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
