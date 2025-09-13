<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Traits\MockableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeadController extends Controller
{
    use MockableController;

    public function __construct()
    {
        $this->initializeMockDataService();
    }

    /**
     * Display a listing of leads with filtering and pagination
     */
    public function index(Request $request)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockIndex($request);
        }

        // Real implementation
        [$page, $perPage] = $this->getPaginationParams($request);
        
        $query = Lead::where('user_id', auth()->id());

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('lead_type')) {
            $query->where('lead_type', $request->lead_type);
        }

        if ($request->has('ai_score_min')) {
            $query->where('ai_score', '>=', $request->ai_score_min);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('property_address', 'LIKE', "%{$search}%");
            });
        }

        $leads = $query->paginate($perPage, ['*'], 'page', $page);

        return $this->successResponse([
            'data' => $leads->items(),
            'meta' => [
                'current_page' => $leads->currentPage(),
                'per_page' => $leads->perPage(),
                'total' => $leads->total(),
                'last_page' => $leads->lastPage(),
                'from' => $leads->firstItem(),
                'to' => $leads->lastItem(),
            ]
        ], 'Leads retrieved successfully');
    }

    /**
     * Store a newly created lead
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_type' => 'required|in:buyer,seller',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'property_address' => 'nullable|string|max:500',
            'property_city' => 'nullable|string|max:100',
            'property_state' => 'nullable|string|max:2',
            'property_zip' => 'nullable|string|max:10',
            'property_type' => 'nullable|in:single_family,townhouse,condo,duplex,multi_family,mobile_home',
            'source' => 'nullable|string|max:100',
            'estimated_value' => 'nullable|numeric|min:0',
            'mortgage_balance' => 'nullable|numeric|min:0',
            'asking_price' => 'nullable|numeric|min:0',
            'preferred_contact_method' => 'nullable|in:phone,email,text'
        ], [
            'lead_type.required' => 'The lead type is required.',
            'lead_type.in' => 'The lead type must be either buyer or seller.',
            'property_type.in' => 'The property type must be one of the following: single_family, townhouse, condo, duplex, multi_family, mobile_home.',
            'preferred_contact_method.in' => 'The preferred contact method must be one of the following: phone, email, text.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'lead validation');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockStore($request);
        }

        // Real implementation
        try {
            $validatedData = $validator->validated();
            $lead = Lead::create(array_merge($validatedData, [
                'user_id' => auth()->id(),
                'uuid' => \Illuminate\Support\Str::uuid(),
                'ai_score' => rand(50, 100), // Mock AI score for now
                'motivation_score' => rand(40, 100),
                'urgency_score' => rand(30, 100),
                'financial_score' => rand(50, 100),
                'status' => 'new'
            ]));

            return $this->successResponse($lead, 'Lead created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'lead operation', 'lead');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('lead operation', $e);
        }
    }

    /**
     * Display the specified lead
     */
    public function show($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockShow($id);
        }

        // Real implementation - only show user's own leads
        $lead = Lead::where('user_id', auth()->id())->find($id);

        if (!$lead) {
            return $this->notFoundResponse('lead', $id);
        }

        return $this->successResponse($lead, 'Lead retrieved successfully');
    }

    /**
     * Update the specified lead
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email',
            'phone' => 'sometimes|string|max:20',
            'property_address' => 'sometimes|string|max:500',
            'property_city' => 'sometimes|string|max:100',
            'property_state' => 'sometimes|string|max:2',
            'property_zip' => 'sometimes|string|max:10',
            'property_type' => 'sometimes|in:single_family,townhouse,condo,duplex,multi_family,mobile_home',
            'source' => 'sometimes|string|max:100',
            'estimated_value' => 'sometimes|numeric|min:0',
            'mortgage_balance' => 'sometimes|numeric|min:0',
            'asking_price' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:new,contacted,qualified,negotiating,contract,closed,dead',
            'preferred_contact_method' => 'sometimes|in:phone,email,text',
            'next_action' => 'sometimes|string',
            'next_action_date' => 'sometimes|date'
        ], [
            'property_type.in' => 'The property type must be one of the following: single_family, townhouse, condo, duplex, multi_family, mobile_home.',
            'preferred_contact_method.in' => 'The preferred contact method must be one of the following: phone, email, text.',
            'status.in' => 'The status must be one of the following: new, contacted, qualified, negotiating, contract, closed, dead.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'lead validation');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockUpdate($request, $id);
        }

        // Real implementation - only update user's own leads
        $lead = Lead::where('user_id', auth()->id())->find($id);

        if (!$lead) {
            return $this->notFoundResponse('lead', $id);
        }

        try {
            $validatedData = $validator->validated();
            $lead->update($validatedData);
            return $this->successResponse($lead, 'Lead updated successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'lead operation', 'lead');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('lead operation', $e);
        }
    }

    /**
     * Remove the specified lead
     */
    public function destroy($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockDestroy($id);
        }

        // Real implementation - only delete user's own leads
        $lead = Lead::where('user_id', auth()->id())->find($id);

        if (!$lead) {
            return $this->notFoundResponse('lead', $id);
        }

        try {
            $lead->delete();
            return $this->successResponse(null, 'Lead deleted successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'lead operation', 'lead');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('lead operation', $e);
        }
    }

    /**
     * Get AI score for a specific lead
     */
    public function aiScore($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockAiScore($id);
        }

        // Real implementation - only analyze user's own leads
        $lead = Lead::where('user_id', auth()->id())->find($id);

        if (!$lead) {
            return $this->notFoundResponse('lead', $id);
        }

        // Mock AI analysis for now
        $analysis = [
            'lead_id' => $lead->id,
            'ai_score' => $lead->ai_score,
            'motivation_score' => $lead->motivation_score,
            'urgency_score' => $lead->urgency_score,
            'financial_score' => $lead->financial_score,
            'analysis' => [
                'motivation_factors' => ['Financial distress', 'Time pressure'],
                'urgency_indicators' => ['Needs to sell within 30 days'],
                'financial_capability' => 'Strong equity position'
            ]
        ];

        return $this->successResponse($analysis, 'Lead AI score retrieved successfully');
    }

    // Mock handlers
    private function handleMockIndex(Request $request)
    {
        [$page, $perPage] = $this->getPaginationParams($request);
        $filters = $this->getFilterParams($request, ['ai_score_min']);

        $result = $this->mockDataService->getLeads($filters, $page, $perPage);
        
        return response()->json($this->formatPaginatedResponse($result, 'Leads retrieved successfully'));
    }

    private function handleMockStore(Request $request)
    {
        try {
            $leadData = array_merge($request->all(), [
                'user_id' => 1, // Mock user ID
                'ai_score' => rand(50, 100),
                'motivation_score' => rand(40, 100),
                'urgency_score' => rand(30, 100),
                'financial_score' => rand(50, 100),
                'status' => 'new',
                'created_at' => now()->format('Y-m-d\TH:i:s.u\Z'),
                'updated_at' => now()->format('Y-m-d\TH:i:s.u\Z'),
            ]);

            $lead = $this->mockDataService->createLead($leadData);
            return $this->successResponse($lead, 'Lead created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'lead operation', 'lead');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('lead operation', $e);
        }
    }

    private function handleMockShow($id)
    {
        $lead = $this->mockDataService->getLead($id);

        if (!$lead) {
            return $this->notFoundResponse('Lead not found');
        }

        return $this->successResponse($lead, 'Lead retrieved successfully');
    }

    private function handleMockUpdate(Request $request, $id)
    {
        $lead = $this->mockDataService->updateLead($id, $request->all());

        if (!$lead) {
            return $this->notFoundResponse('Lead not found');
        }

        return $this->successResponse($lead, 'Lead updated successfully');
    }

    private function handleMockDestroy($id)
    {
        $deleted = $this->mockDataService->deleteLead($id);

        if (!$deleted) {
            return $this->notFoundResponse('Lead not found');
        }

        return $this->successResponse(null, 'Lead deleted successfully');
    }

    private function handleMockAiScore($id)
    {
        $analysis = $this->mockDataService->getLeadAiScore($id);

        if (!$analysis) {
            return $this->notFoundResponse('Lead not found');
        }

        return $this->successResponse($analysis, 'Lead AI score retrieved successfully');
    }
}
