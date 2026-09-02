<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventCategory\StoreEventCategoryRequest;
use App\Http\Requests\EventCategory\UpdateEventCategoryRequest;
use App\Http\Resources\EventCategoryResource;
use App\Http\Resources\EventResource;
use App\Models\EventCategory;
use App\Services\EventCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventCategoryController extends Controller
{
    public function __construct(
        private readonly EventCategoryService $eventCategoryService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'sort', 'direction', 'per_page', 'page']);

        // If pagination requested explicitly or per_page present, paginate; else support both via paginated response for consistency
        $paginator = $this->eventCategoryService->paginate($filters);

        // If client didn't request pagination, still return paginated structure for consistency (admin UI expects pagination)
        return response()->json([
            'status' => 'success',
            'message' => 'Event categories retrieved successfully.',
            'data' => EventCategoryResource::collection($paginator->items()),
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
        $category = $this->eventCategoryService->findBySlug($slug);

        if (! $category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event category not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Event category retrieved successfully.',
            'data' => new EventCategoryResource($category),
        ]);
    }

    public function events(string $slug): JsonResponse
    {
        $category = $this->eventCategoryService->findBySlugWithEvents($slug);

        if (! $category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event category not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Events retrieved by category successfully.',
            'data' => EventResource::collection($category->events),
        ]);
    }

    public function store(StoreEventCategoryRequest $request): JsonResponse
    {
        $category = $this->eventCategoryService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Event category created successfully.',
            'data' => new EventCategoryResource($category),
        ], 201);
    }

    public function update(UpdateEventCategoryRequest $request, EventCategory $eventCategory): JsonResponse
    {
        $updated = $this->eventCategoryService->update($eventCategory, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Event category updated successfully.',
            'data' => new EventCategoryResource($updated),
        ]);
    }

    public function destroy(EventCategory $eventCategory): JsonResponse
    {
        $this->eventCategoryService->delete($eventCategory);

        return response()->json([
            'status' => 'success',
            'message' => 'Event category deleted successfully.',
            'data' => null,
        ]);
    }
}
