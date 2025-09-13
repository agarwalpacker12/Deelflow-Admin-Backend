<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DealMilestone;
use App\Traits\MockableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DealMilestoneController extends Controller
{
    use MockableController;

    public function __construct()
    {
        $this->initializeMockDataService();
    }

    /**
     * Display a listing of deal milestones with filtering and pagination
     */
    public function index(Request $request)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockIndex($request);
        }

        // Real implementation
        [$page, $perPage] = $this->getPaginationParams($request);
        
        $query = DealMilestone::with(['deal', 'completedBy'])
            ->whereHas('deal', function($q) {
                $q->where('buyer_id', auth()->id())
                  ->orWhere('seller_id', auth()->id())
                  ->orWhere('funder_id', auth()->id());
            });

        // Apply filters
        if ($request->has('deal_id')) {
            $query->where('deal_id', $request->deal_id);
        }

        if ($request->has('milestone_type')) {
            $query->where('milestone_type', $request->milestone_type);
        }

        if ($request->has('is_critical')) {
            $query->where('is_critical', $request->boolean('is_critical'));
        }

        if ($request->has('completed')) {
            if ($request->boolean('completed')) {
                $query->whereNotNull('completed_at');
            } else {
                $query->whereNull('completed_at');
            }
        }

        $milestones = $query->paginate($perPage, ['*'], 'page', $page);

        return $this->successResponse([
            'data' => $milestones->items(),
            'meta' => [
                'current_page' => $milestones->currentPage(),
                'per_page' => $milestones->perPage(),
                'total' => $milestones->total(),
                'last_page' => $milestones->lastPage(),
                'from' => $milestones->firstItem(),
                'to' => $milestones->lastItem(),
            ]
        ], 'Deal milestones retrieved successfully');
    }

    /**
     * Store a newly created deal milestone
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deal_id' => 'required|exists:deals,id',
            'milestone_type' => 'required|in:inspection,appraisal,financing,title,closing,contract_signed,earnest_deposited,custom',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'is_critical' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'dealmilestone validation');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockStore($request);
        }

        // Real implementation
        try {
            $validatedData = $validator->validated();
            $milestone = DealMilestone::create($validatedData);
            $milestone->load(['deal', 'completedBy']);

            return $this->successResponse($milestone, 'Deal milestone created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'dealmilestone operation', 'dealmilestone');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('dealmilestone operation', $e);
        }
    }

    /**
     * Display the specified deal milestone
     */
    public function show($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockShow($id);
        }

        // Real implementation - only show milestones for deals user is involved in
        $milestone = DealMilestone::with(['deal', 'completedBy'])
            ->whereHas('deal', function($q) {
                $q->where('buyer_id', auth()->id())
                  ->orWhere('seller_id', auth()->id())
                  ->orWhere('funder_id', auth()->id());
            })
            ->find($id);

        if (!$milestone) {
            return $this->notFoundResponse('Deal milestone not found');
        }

        return $this->successResponse($milestone, 'Deal milestone retrieved successfully');
    }

    /**
     * Update the specified deal milestone
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'deal_id' => 'sometimes|exists:deals,id',
            'milestone_type' => 'sometimes|in:inspection,appraisal,financing,title,closing,contract_signed,earnest_deposited,custom',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'due_date' => 'sometimes|date',
            'is_critical' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'dealmilestone validation');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockUpdate($request, $id);
        }

        // Real implementation - only update milestones for deals user is involved in
        $milestone = DealMilestone::whereHas('deal', function($q) {
                $q->where('buyer_id', auth()->id())
                  ->orWhere('seller_id', auth()->id())
                  ->orWhere('funder_id', auth()->id());
            })
            ->find($id);

        if (!$milestone) {
            return $this->notFoundResponse('Deal milestone not found');
        }

        try {
            $validatedData = $validator->validated();
            $milestone->update($validatedData);
            $milestone->load(['deal', 'completedBy']);
            
            return $this->successResponse($milestone, 'Deal milestone updated successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'dealmilestone operation', 'dealmilestone');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('dealmilestone operation', $e);
        }
    }

    /**
     * Remove the specified deal milestone
     */
    public function destroy($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockDestroy($id);
        }

        // Real implementation - only delete milestones for deals user is involved in
        $milestone = DealMilestone::whereHas('deal', function($q) {
                $q->where('buyer_id', auth()->id())
                  ->orWhere('seller_id', auth()->id())
                  ->orWhere('funder_id', auth()->id());
            })
            ->find($id);

        if (!$milestone) {
            return $this->notFoundResponse('Deal milestone not found');
        }

        try {
            $milestone->delete();
            return $this->successResponse(null, 'Deal milestone deleted successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'dealmilestone operation', 'dealmilestone');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('dealmilestone operation', $e);
        }
    }

    /**
     * Mark a milestone as completed
     */
    public function complete($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockComplete($id);
        }

        // Real implementation - only complete milestones for deals user is involved in
        $milestone = DealMilestone::whereHas('deal', function($q) {
                $q->where('buyer_id', auth()->id())
                  ->orWhere('seller_id', auth()->id())
                  ->orWhere('funder_id', auth()->id());
            })
            ->find($id);

        if (!$milestone) {
            return $this->notFoundResponse('Deal milestone not found');
        }

        if ($milestone->completed_at) {
            return $this->errorResponse('Milestone is already completed', null, 400);
        }

        try {
            $milestone->update([
                'completed_at' => now(),
                'completed_by' => auth()->id(),
            ]);

            $milestone->load(['deal', 'completedBy']);
            
            return $this->successResponse($milestone, 'Deal milestone completed successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'dealmilestone operation', 'dealmilestone');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('dealmilestone operation', $e);
        }
    }

    // Mock handlers
    private function handleMockIndex(Request $request)
    {
        [$page, $perPage] = $this->getPaginationParams($request);
        $filters = $this->getFilterParams($request, ['deal_id', 'milestone_type', 'is_critical', 'completed']);

        $result = $this->mockDataService->getDealMilestones($filters, $page, $perPage);
        
        return response()->json($this->formatPaginatedResponse($result, 'Deal milestones retrieved successfully'));
    }

    private function handleMockStore(Request $request)
    {
        try {
            $milestoneData = array_merge($request->all(), [
                'created_at' => now()->format('Y-m-d\TH:i:s.u\Z'),
                'updated_at' => now()->format('Y-m-d\TH:i:s.u\Z'),
            ]);

            $milestone = $this->mockDataService->createDealMilestone($milestoneData);
            return $this->successResponse($milestone, 'Deal milestone created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'dealmilestone operation', 'dealmilestone');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('dealmilestone operation', $e);
        }
    }

    private function handleMockShow($id)
    {
        $milestone = $this->mockDataService->getDealMilestone($id);

        if (!$milestone) {
            return $this->notFoundResponse('Deal milestone not found');
        }

        return $this->successResponse($milestone, 'Deal milestone retrieved successfully');
    }

    private function handleMockUpdate(Request $request, $id)
    {
        $milestone = $this->mockDataService->updateDealMilestone($id, $request->all());

        if (!$milestone) {
            return $this->notFoundResponse('Deal milestone not found');
        }

        return $this->successResponse($milestone, 'Deal milestone updated successfully');
    }

    private function handleMockDestroy($id)
    {
        $deleted = $this->mockDataService->deleteDealMilestone($id);

        if (!$deleted) {
            return $this->notFoundResponse('Deal milestone not found');
        }

        return $this->successResponse(null, 'Deal milestone deleted successfully');
    }

    private function handleMockComplete($id)
    {
        $milestone = $this->mockDataService->completeDealMilestone($id, 1); // Mock user ID

        if (!$milestone) {
            return $this->notFoundResponse('Deal milestone not found');
        }

        if (isset($milestone['error'])) {
            return $this->errorResponse($milestone['error'], 400);
        }

        return $this->successResponse($milestone, 'Deal milestone completed successfully');
    }
}
