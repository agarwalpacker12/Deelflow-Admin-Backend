<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAchievement;
use App\Models\User;
use App\Traits\MockableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserAchievementController extends Controller
{
    use MockableController;

    public function __construct()
    {
        $this->initializeMockDataService();
    }

    /**
     * Display a listing of user achievements with summary
     */
    public function index(Request $request)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockIndex($request);
        }

        // Real implementation
        [$page, $perPage] = $this->getPaginationParams($request);
        
        $query = UserAchievement::with(['user'])
            ->where('user_id', auth()->id());

        // Apply filters
        if ($request->has('achievement_type')) {
            $query->where('achievement_type', $request->achievement_type);
        }

        $achievements = $query->orderBy('earned_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Get user summary
        $user = User::find(auth()->id());
        $totalPoints = UserAchievement::where('user_id', auth()->id())->sum('points_earned');
        $totalAchievements = UserAchievement::where('user_id', auth()->id())->count();
        
        // Calculate level based on points (every 500 points = 1 level)
        $currentLevel = floor($totalPoints / 500) + 1;
        $pointsToNextLevel = (500 * $currentLevel) - $totalPoints;

        $summary = [
            'total_points' => $totalPoints,
            'current_level' => $currentLevel,
            'points_to_next_level' => $pointsToNextLevel,
            'total_achievements' => $totalAchievements
        ];

        return $this->successResponse([
            'data' => $achievements->items(),
            'summary' => $summary,
            'meta' => [
                'current_page' => $achievements->currentPage(),
                'per_page' => $achievements->perPage(),
                'total' => $achievements->total(),
                'last_page' => $achievements->lastPage(),
                'from' => $achievements->firstItem(),
                'to' => $achievements->lastItem(),
            ]
        ], 'User achievements retrieved successfully');
    }

    /**
     * Store a newly created achievement (typically called by system)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'achievement_type' => 'required|in:deal_milestone,lead_conversion,property_listing,campaign_success,referral,login_streak,profile_completion,deal_closed',
            'achievement_name' => 'required|string|max:255',
            'points_earned' => 'required|integer|min:1',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'userachievement validation');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockStore($request);
        }

        // Real implementation
        try {
            $validatedData = $validator->validated();
            $achievement = UserAchievement::create(array_merge($validatedData, [
                'user_id' => auth()->id(),
            ]));

            $achievement->load(['user']);

            return $this->successResponse($achievement, 'Achievement earned successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'userachievement operation', 'userachievement');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('userachievement operation', $e);
        }
    }

    /**
     * Display the specified achievement
     */
    public function show($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockShow($id);
        }

        // Real implementation
        $achievement = UserAchievement::with(['user'])
            ->where('user_id', auth()->id())
            ->find($id);

        if (!$achievement) {
            return $this->notFoundResponse('Achievement not found');
        }

        return $this->successResponse($achievement, 'Achievement retrieved successfully');
    }

    /**
     * Update method is not typically needed for achievements as they are earned, not modified
     */
    public function update(Request $request, $id)
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Update operation is not supported for achievements'
        ], 405);
    }

    /**
     * Remove the specified achievement (admin only operation)
     */
    public function destroy($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockDestroy($id);
        }

        // Real implementation - typically only admins should be able to delete achievements
        $achievement = UserAchievement::where('user_id', auth()->id())->find($id);

        if (!$achievement) {
            return $this->notFoundResponse('Achievement not found');
        }

        try {
            $achievement->delete();
            return $this->successResponse(null, 'Achievement deleted successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'userachievement operation', 'userachievement');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('userachievement operation', $e);
        }
    }

    // Mock handlers
    private function handleMockIndex(Request $request)
    {
        [$page, $perPage] = $this->getPaginationParams($request);
        $filters = array_merge(
            ['user_id' => 1], // Mock user ID
            $this->getFilterParams($request, ['achievement_type'])
        );

        $result = $this->mockDataService->getUserAchievements($filters, $page, $perPage);
        
        // Add summary data
        $summary = [
            'total_points' => 350,
            'current_level' => 2,
            'points_to_next_level' => 150,
            'total_achievements' => 5
        ];

        $response = $this->formatPaginatedResponse($result, 'User achievements retrieved successfully');
        $response['data']['summary'] = $summary;
        
        return response()->json($response);
    }

    private function handleMockStore(Request $request)
    {
        try {
            $achievementData = array_merge($request->all(), [
                'user_id' => 1, // Mock user ID
                'earned_at' => now()->format('Y-m-d\TH:i:s.u\Z'),
            ]);

            $achievement = $this->mockDataService->createUserAchievement($achievementData);
            return $this->successResponse($achievement, 'Achievement earned successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'userachievement operation', 'userachievement');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('userachievement operation', $e);
        }
    }

    private function handleMockShow($id)
    {
        $achievement = $this->mockDataService->getUserAchievement($id);

        if (!$achievement || $achievement['user_id'] !== 1) { // Mock user ID check
            return $this->notFoundResponse('Achievement not found');
        }

        return $this->successResponse($achievement, 'Achievement retrieved successfully');
    }

    private function handleMockDestroy($id)
    {
        $achievement = $this->mockDataService->getUserAchievement($id);

        if (!$achievement || $achievement['user_id'] !== 1) { // Mock user ID check
            return $this->notFoundResponse('Achievement not found');
        }

        $deleted = $this->mockDataService->deleteUserAchievement($id);

        if (!$deleted) {
            return $this->notFoundResponse('Achievement not found');
        }

        return $this->successResponse(null, 'Achievement deleted successfully');
    }
}
