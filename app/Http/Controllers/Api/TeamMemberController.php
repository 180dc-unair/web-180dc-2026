<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeamMember\StoreTeamMemberRequest;
use App\Http\Requests\TeamMember\UpdateTeamMemberRequest;
use App\Http\Resources\TeamMemberResource;
use App\Models\TeamMember;
use App\Services\TeamMemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    public function __construct(
        private readonly TeamMemberService $teamMemberService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $isAdmin = $request->user()?->role === 'admin';

        $filters = $request->only(['search', 'is_active', 'sort', 'direction', 'per_page', 'page']);

        $paginator = $this->teamMemberService->paginate($filters, $isAdmin);

        return response()->json([
            'status' => 'success',
            'message' => 'Team members retrieved successfully.',
            'data' => TeamMemberResource::collection($paginator->items()),
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

    public function show(Request $request, string $id): JsonResponse
    {
        $isAdmin = $request->user()?->role === 'admin';

        $member = $this->teamMemberService->findById($id, $isAdmin);

        if (! $member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Team member not found.',
                'data' => null,
            ], 404);
        }

        // Public cannot see inactive
        if (! $isAdmin && ! $member->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Team member not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Team member retrieved successfully.',
            'data' => new TeamMemberResource($member),
        ]);
    }

    public function store(StoreTeamMemberRequest $request): JsonResponse
    {
        $member = $this->teamMemberService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Team member created successfully.',
            'data' => new TeamMemberResource($member->load('image')),
        ], 201);
    }

    public function update(UpdateTeamMemberRequest $request, TeamMember $teamMember): JsonResponse
    {
        $updated = $this->teamMemberService->update($teamMember, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Team member updated successfully.',
            'data' => new TeamMemberResource($updated),
        ]);
    }

    public function destroy(TeamMember $teamMember): JsonResponse
    {
        $this->teamMemberService->delete($teamMember);

        return response()->json([
            'status' => 'success',
            'message' => 'Team member deleted successfully.',
            'data' => null,
        ]);
    }
}
