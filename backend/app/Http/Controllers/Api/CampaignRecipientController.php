<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CampaignRecipient;
use App\Models\Campaign;
use App\Traits\MockableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CampaignRecipientController extends Controller
{
    use MockableController;

    public function __construct()
    {
        $this->initializeMockDataService();
    }

    /**
     * Display a listing of campaign recipients with filtering and pagination
     */
    public function index(Request $request)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockIndex($request);
        }

        // Real implementation
        [$page, $perPage] = $this->getPaginationParams($request);
        
        $query = CampaignRecipient::with(['campaign', 'lead']);

        // Filter by user's campaigns only
        $query->whereHas('campaign', function($q) {
            $q->where('user_id', auth()->id());
        });

        // Apply filters
        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->has('sent')) {
            if ($request->boolean('sent')) {
                $query->whereNotNull('sent_at');
            } else {
                $query->whereNull('sent_at');
            }
        }

        if ($request->has('opened')) {
            if ($request->boolean('opened')) {
                $query->whereNotNull('opened_at');
            } else {
                $query->whereNull('opened_at');
            }
        }

        if ($request->has('clicked')) {
            if ($request->boolean('clicked')) {
                $query->whereNotNull('clicked_at');
            } else {
                $query->whereNull('clicked_at');
            }
        }

        if ($request->has('responded')) {
            if ($request->boolean('responded')) {
                $query->whereNotNull('responded_at');
            } else {
                $query->whereNull('responded_at');
            }
        }

        $recipients = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return $this->successResponse([
            'data' => $recipients->items(),
            'meta' => [
                'current_page' => $recipients->currentPage(),
                'per_page' => $recipients->perPage(),
                'total' => $recipients->total(),
                'last_page' => $recipients->lastPage(),
                'from' => $recipients->firstItem(),
                'to' => $recipients->lastItem(),
            ]
        ], 'Campaign recipients retrieved successfully');
    }

    /**
     * Add recipients to a campaign
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campaign_id' => 'required|exists:campaigns,id',
            'lead_ids' => 'required|array|min:1',
            'lead_ids.*' => 'exists:leads,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'campaignrecipient validation');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockStore($request);
        }

        // Real implementation
        try {
            // Verify campaign belongs to authenticated user
            $campaign = Campaign::where('user_id', auth()->id())
                ->find($request->campaign_id);

            if (!$campaign) {
                return $this->notFoundResponse('Campaign not found');
            }

            $recipients = [];
            $existingCount = 0;

            foreach ($request->lead_ids as $leadId) {
                // Check if recipient already exists
                $existing = CampaignRecipient::where('campaign_id', $request->campaign_id)
                    ->where('lead_id', $leadId)
                    ->first();

                if ($existing) {
                    $existingCount++;
                    continue;
                }

                $recipient = CampaignRecipient::create([
                    'campaign_id' => $request->campaign_id,
                    'lead_id' => $leadId,
                    'open_count' => 0,
                    'click_count' => 0,
                ]);

                $recipient->load(['campaign', 'lead']);
                $recipients[] = $recipient;
            }

            // Update campaign total recipients count
            $campaign->update([
                'total_recipients' => CampaignRecipient::where('campaign_id', $campaign->id)->count()
            ]);

            $message = count($recipients) . ' recipients added successfully';
            if ($existingCount > 0) {
                $message .= " ({$existingCount} were already recipients)";
            }

            return $this->successResponse($recipients, $message, 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'campaignrecipient operation', 'campaignrecipient');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('campaignrecipient operation', $e);
        }
    }

    /**
     * Display the specified campaign recipient
     */
    public function show($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockShow($id);
        }

        // Real implementation
        $recipient = CampaignRecipient::with(['campaign', 'lead'])
            ->whereHas('campaign', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->find($id);

        if (!$recipient) {
            return $this->notFoundResponse('Campaign recipient not found');
        }

        return $this->successResponse($recipient, 'Campaign recipient retrieved successfully');
    }

    /**
     * Update the specified campaign recipient (typically for tracking engagement)
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'sent_at' => 'sometimes|date',
            'delivered_at' => 'sometimes|date',
            'opened_at' => 'sometimes|date',
            'clicked_at' => 'sometimes|date',
            'responded_at' => 'sometimes|date',
            'converted_at' => 'sometimes|date',
            'open_count' => 'sometimes|integer|min:0',
            'click_count' => 'sometimes|integer|min:0',
            'response_data' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'campaignrecipient validation');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockUpdate($request, $id);
        }

        // Real implementation
        $recipient = CampaignRecipient::whereHas('campaign', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->find($id);

        if (!$recipient) {
            return $this->notFoundResponse('Campaign recipient not found');
        }

        try {
            $validatedData = $validator->validated();
            $recipient->update($validatedData);
            $recipient->load(['campaign', 'lead']);
            
            return $this->successResponse($recipient, 'Campaign recipient updated successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'campaignrecipient operation', 'campaignrecipient');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('campaignrecipient operation', $e);
        }
    }

    /**
     * Remove the specified campaign recipient
     */
    public function destroy($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockDestroy($id);
        }

        // Real implementation
        $recipient = CampaignRecipient::whereHas('campaign', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->find($id);

        if (!$recipient) {
            return $this->notFoundResponse('Campaign recipient not found');
        }

        try {
            $campaignId = $recipient->campaign_id;
            $recipient->delete();

            // Update campaign total recipients count
            $campaign = Campaign::find($campaignId);
            if ($campaign) {
                $campaign->update([
                    'total_recipients' => CampaignRecipient::where('campaign_id', $campaignId)->count()
                ]);
            }

            return $this->successResponse(null, 'Campaign recipient removed successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'campaignrecipient operation', 'campaignrecipient');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('campaignrecipient operation', $e);
        }
    }

    // Mock handlers
    private function handleMockIndex(Request $request)
    {
        [$page, $perPage] = $this->getPaginationParams($request);
        $filters = $this->getFilterParams($request, ['campaign_id', 'sent', 'opened', 'clicked', 'responded']);

        $result = $this->mockDataService->getCampaignRecipients($filters, $page, $perPage);
        
        return response()->json($this->formatPaginatedResponse($result, 'Campaign recipients retrieved successfully'));
    }

    private function handleMockStore(Request $request)
    {
        try {
            $recipients = [];
            $existingCount = 0;

            foreach ($request->lead_ids as $leadId) {
                // Check if recipient already exists in mock data
                $existing = $this->mockDataService->getCampaignRecipientByIds($request->campaign_id, $leadId);
                
                if ($existing) {
                    $existingCount++;
                    continue;
                }

                $recipientData = [
                    'campaign_id' => $request->campaign_id,
                    'lead_id' => $leadId,
                    'open_count' => 0,
                    'click_count' => 0,
                    'created_at' => now()->format('Y-m-d\TH:i:s.u\Z'),
                    'updated_at' => now()->format('Y-m-d\TH:i:s.u\Z'),
                ];

                $recipient = $this->mockDataService->createCampaignRecipient($recipientData);
                $recipients[] = $recipient;
            }

            $message = count($recipients) . ' recipients added successfully';
            if ($existingCount > 0) {
                $message .= " ({$existingCount} were already recipients)";
            }

            return $this->successResponse($recipients, $message, 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'campaignrecipient operation', 'campaignrecipient');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('campaignrecipient operation', $e);
        }
    }

    private function handleMockShow($id)
    {
        $recipient = $this->mockDataService->getCampaignRecipient($id);

        if (!$recipient) {
            return $this->notFoundResponse('Campaign recipient not found');
        }

        return $this->successResponse($recipient, 'Campaign recipient retrieved successfully');
    }

    private function handleMockUpdate(Request $request, $id)
    {
        $recipient = $this->mockDataService->updateCampaignRecipient($id, $request->all());

        if (!$recipient) {
            return $this->notFoundResponse('Campaign recipient not found');
        }

        return $this->successResponse($recipient, 'Campaign recipient updated successfully');
    }

    private function handleMockDestroy($id)
    {
        $deleted = $this->mockDataService->deleteCampaignRecipient($id);

        if (!$deleted) {
            return $this->notFoundResponse('Campaign recipient not found');
        }

        return $this->successResponse(null, 'Campaign recipient removed successfully');
    }
}
