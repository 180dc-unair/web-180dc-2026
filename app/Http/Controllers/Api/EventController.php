<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService $eventService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $isAdmin = $request->user()?->role === 'admin';

        $filters = $request->only([
            'search',
            'category_id',
            'category_slug',
            'status',
            'is_featured',
            'is_paid',
            'sort',
            'direction',
            'per_page',
            'page',
        ]);

        $paginator = $this->eventService->paginate($filters, $isAdmin);

        return response()->json([
            'status' => 'success',
            'message' => 'Events retrieved successfully.',
            'data' => EventResource::collection($paginator->items()),
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

    public function show(Request $request, string $slug): JsonResponse
    {
        $isAdmin = $request->user()?->role === 'admin';

        $event = $this->eventService->findBySlug($slug, $isAdmin);

        if (! $event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Event retrieved successfully.',
            'data' => new EventResource($event),
        ]);
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = $this->eventService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Event created successfully.',
            'data' => new EventResource($event->load(['category', 'image'])),
        ], 201);
    }

    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $updated = $this->eventService->update($event, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Event updated successfully.',
            'data' => new EventResource($updated),
        ]);
    }

    public function destroy(Event $event): JsonResponse
    {
        $this->eventService->delete($event);

        return response()->json([
            'status' => 'success',
            'message' => 'Event deleted successfully.',
            'data' => null,
        ]);
    }
}
