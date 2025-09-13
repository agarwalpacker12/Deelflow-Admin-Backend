<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Traits\MockableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CampaignController extends Controller
{
    use MockableController;

    public function __construct()
    {
        $this->initializeMockDataService();
    }

    /**
     * Display a listing of campaigns with filtering and pagination
     */
    public function index(Request $request)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockIndex($request);
        }

        // Real implementation
        [$page, $perPage] = $this->getPaginationParams($request);
        
        $query = Campaign::with(['user', 'recipients'])
            ->where('user_id', auth()->id());

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('campaign_type')) {
            $query->where('campaign_type', $request->campaign_type);
        }

        if ($request->has('channel')) {
            $query->where('channel', $request->channel);
        }

        $campaigns = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return $this->successResponse([
            'data' => $campaigns->items(),
            'meta' => [
                'current_page' => $campaigns->currentPage(),
                'per_page' => $campaigns->perPage(),
                'total' => $campaigns->total(),
                'last_page' => $campaigns->lastPage(),
                'from' => $campaigns->firstItem(),
                'to' => $campaigns->lastItem(),
            ]
        ], 'Campaigns retrieved successfully');
    }

    /**
     * Store a newly created campaign
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'campaign_type' => 'required|in:seller_finder,buyer_finder',
            'channel' => 'required|in:email,sms,voice,direct_mail',
            'target_criteria' => 'nullable|array',
            'subject_line' => 'required_if:channel,email|string|max:255',
            'email_content' => 'required_if:channel,email|string',
            'sms_content' => 'required_if:channel,sms|string|max:160',
            'voice_script' => 'required_if:channel,voice|string',
            'scheduled_at' => 'nullable|date|after:now',
            'budget' => 'nullable|numeric|min:0',
            'use_ai_personalization' => 'nullable|boolean',
            'ai_tone' => 'nullable|in:professional,friendly,urgent,casual',
        ];

        $messages = [
            'campaign_type.in' => 'The selected campaign type is invalid. Allowed values are: seller_finder, buyer_finder.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'campaign validation');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockStore($request);
        }

        // Real implementation
        try {
            $validatedData = $validator->validated();
            $campaign = Campaign::create(array_merge($validatedData, [
                'user_id' => auth()->id(),
                'status' => 'draft',
                'total_recipients' => 0,
                'sent_count' => 0,
                'open_count' => 0,
                'click_count' => 0,
                'response_count' => 0,
                'conversion_count' => 0,
                'spent' => 0.00,
            ]));

            $campaign->load(['user', 'recipients']);

            return $this->successResponse($campaign, 'Campaign created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'campaign operation', 'campaign');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('campaign operation', $e);
        }
    }

    /**
     * Display the specified campaign
     */
    public function show($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockShow($id);
        }

        // Real implementation
        $campaign = Campaign::with(['user', 'recipients'])
            ->where('user_id', auth()->id())
            ->find($id);

        if (!$campaign) {
            return $this->notFoundResponse('Campaign not found');
        }

        return $this->successResponse($campaign, 'Campaign retrieved successfully');
    }

    /**
     * Update the specified campaign
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'sometimes|string|max:255',
            'campaign_type' => 'sometimes|in:seller_finder,buyer_finder',
            'channel' => 'sometimes|in:email,sms,voice,direct_mail',
            'target_criteria' => 'sometimes|array',
            'subject_line' => 'sometimes|string|max:255',
            'email_content' => 'sometimes|string',
            'sms_content' => 'sometimes|string|max:160',
            'voice_script' => 'sometimes|string',
            'scheduled_at' => 'sometimes|date',
            'budget' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:draft,scheduled,active,paused,completed,cancelled',
            'use_ai_personalization' => 'sometimes|boolean',
            'ai_tone' => 'sometimes|in:professional,friendly,urgent,casual',
        ];

        $messages = [
            'campaign_type.in' => 'The selected campaign type is invalid. Allowed values are: seller_finder, buyer_finder.',
            'status.in' => 'The selected status is invalid. Allowed values are: draft, scheduled, active, paused, completed, cancelled.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'campaign validation');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockUpdate($request, $id);
        }

        // Real implementation
        $campaign = Campaign::where('user_id', auth()->id())->find($id);

        if (!$campaign) {
            return $this->notFoundResponse('Campaign not found');
        }

        // Prevent editing active or completed campaigns
        if (in_array($campaign->status, ['active', 'completed'])) {
            return $this->errorResponse('Cannot edit active or completed campaigns', 400);
        }

        try {
            $validatedData = $validator->validated();
            $campaign->update($validatedData);
            $campaign->load(['user', 'recipients']);
            
            return $this->successResponse($campaign, 'Campaign updated successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'campaign operation', 'campaign');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('campaign operation', $e);
        }
    }

    /**
     * Remove the specified campaign
     */
    public function destroy($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockDestroy($id);
        }

        // Real implementation
        $campaign = Campaign::where('user_id', auth()->id())->find($id);

        if (!$campaign) {
            return $this->notFoundResponse('Campaign not found');
        }

        // Prevent deleting active campaigns
        if ($campaign->status === 'active') {
            return $this->errorResponse('Cannot delete active campaigns', 400);
        }

        try {
            $campaign->delete();
            return $this->successResponse(null, 'Campaign deleted successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'campaign operation', 'campaign');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('campaign operation', $e);
        }
    }

    /**
     * Get recipients for a specific campaign
     */
    public function recipients($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockRecipients($id);
        }

        // Real implementation
        $campaign = Campaign::with(['recipients.lead'])
            ->where('user_id', auth()->id())
            ->find($id);

        if (!$campaign) {
            return $this->notFoundResponse('Campaign not found');
        }

        return $this->successResponse($campaign->recipients, 'Campaign recipients retrieved successfully');
    }

    // Mock handlers
    private function handleMockIndex(Request $request)
    {
        [$page, $perPage] = $this->getPaginationParams($request);
        $filters = array_merge(
            ['user_id' => 1], // Mock user ID
            $this->getFilterParams($request, ['status', 'campaign_type', 'channel'])
        );

        $result = $this->mockDataService->getCampaigns($filters, $page, $perPage);
        
        return response()->json($this->formatPaginatedResponse($result, 'Campaigns retrieved successfully'));
    }

    private function handleMockStore(Request $request)
    {
        try {
            $campaignData = array_merge($request->all(), [
                'user_id' => 1, // Mock user ID
                'status' => 'draft',
                'total_recipients' => 0,
                'sent_count' => 0,
                'open_count' => 0,
                'click_count' => 0,
                'response_count' => 0,
                'conversion_count' => 0,
                'spent' => 0.00,
                'created_at' => now()->format('Y-m-d\TH:i:s.u\Z'),
                'updated_at' => now()->format('Y-m-d\TH:i:s.u\Z'),
            ]);

            $campaign = $this->mockDataService->createCampaign($campaignData);
            return $this->successResponse($campaign, 'Campaign created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'campaign operation', 'campaign');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('campaign operation', $e);
        }
    }

    private function handleMockShow($id)
    {
        $campaign = $this->mockDataService->getCampaign($id);

        if (!$campaign || $campaign['user_id'] !== 1) { // Mock user ID check
            return $this->notFoundResponse('Campaign not found');
        }

        return $this->successResponse($campaign, 'Campaign retrieved successfully');
    }

    private function handleMockUpdate(Request $request, $id)
    {
        $campaign = $this->mockDataService->getCampaign($id);

        if (!$campaign || $campaign['user_id'] !== 1) { // Mock user ID check
            return $this->notFoundResponse('Campaign not found');
        }

        // Prevent editing active or completed campaigns
        if (in_array($campaign['status'], ['active', 'completed'])) {
            return $this->errorResponse('Cannot edit active or completed campaigns', 400);
        }

        $updatedCampaign = $this->mockDataService->updateCampaign($id, $request->all());

        return $this->successResponse($updatedCampaign, 'Campaign updated successfully');
    }

    private function handleMockDestroy($id)
    {
        $campaign = $this->mockDataService->getCampaign($id);

        if (!$campaign || $campaign['user_id'] !== 1) { // Mock user ID check
            return $this->notFoundResponse('Campaign not found');
        }

        // Prevent deleting active campaigns
        if ($campaign['status'] === 'active') {
            return $this->errorResponse('Cannot delete active campaigns', 400);
        }

        $deleted = $this->mockDataService->deleteCampaign($id);

        if (!$deleted) {
            return $this->notFoundResponse('Campaign not found');
        }

        return $this->successResponse(null, 'Campaign deleted successfully');
    }

    private function handleMockRecipients($id)
    {
        $recipients = $this->mockDataService->getCampaignRecipients($id);

        if ($recipients === null) {
            return $this->notFoundResponse('Campaign not found');
        }

        return $this->successResponse($recipients, 'Campaign recipients retrieved successfully');
    }
}
