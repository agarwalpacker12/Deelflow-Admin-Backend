<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Traits\MockableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiConversationController extends Controller
{
    use MockableController;

    public function __construct()
    {
        $this->initializeMockDataService();
    }

    /**
     * Display a listing of AI conversations with filtering and pagination
     */
    public function index(Request $request)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockIndex($request);
        }

        // Real implementation
        [$page, $perPage] = $this->getPaginationParams($request);
        
        $query = AiConversation::with(['user', 'lead', 'property'])
            ->where('user_id', auth()->id());

        // Apply filters
        if ($request->has('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->has('outcome')) {
            $query->where('outcome', $request->outcome);
        }

        if ($request->has('transferred_to_human')) {
            $query->where('transferred_to_human', $request->boolean('transferred_to_human'));
        }

        $conversations = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return $this->successResponse([
            'data' => $conversations->items(),
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
                'last_page' => $conversations->lastPage(),
                'from' => $conversations->firstItem(),
                'to' => $conversations->lastItem(),
            ]
        ], 'AI conversations retrieved successfully');
    }

    /**
     * Store a newly created AI conversation
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'nullable|exists:leads,id',
            'property_id' => 'nullable|exists:properties,id',
            'channel' => 'required|in:sms,voice,email,chat,whatsapp',
            'external_id' => 'nullable|string|max:255',
            'messages' => 'nullable|array',
            'sentiment_score' => 'nullable|integer|min:0|max:100',
            'urgency_score' => 'nullable|integer|min:0|max:100',
            'motivation_score' => 'nullable|integer|min:0|max:100',
            'qualification_score' => 'nullable|integer|min:0|max:100',
            'extracted_data' => 'nullable|array',
            'identified_pain_points' => 'nullable|array',
            'detected_keywords' => 'nullable|array',
            'status' => 'nullable|in:active,completed,transferred,failed',
            'outcome' => 'nullable|in:qualified_lead,appointment_scheduled,not_interested,callback_requested,transferred',
            'next_steps' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'aiconversation validation');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockStore($request);
        }

        // Real implementation
        try {
            $validatedData = $validator->validated();
            $conversation = AiConversation::create(array_merge($validatedData, [
                'user_id' => auth()->id(),
                'uuid' => \Illuminate\Support\Str::uuid(),
                'status' => $request->status ?? 'active',
                'transferred_to_human' => false,
            ]));

            $conversation->load(['user', 'lead', 'property']);

            return $this->successResponse($conversation, 'AI conversation created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'aiconversation operation', 'aiconversation');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('aiconversation operation', $e);
        }
    }

    /**
     * Display the specified AI conversation
     */
    public function show($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockShow($id);
        }

        // Real implementation
        $conversation = AiConversation::with(['user', 'lead', 'property'])
            ->where('user_id', auth()->id())
            ->find($id);

        if (!$conversation) {
            return $this->notFoundResponse('AI conversation not found');
        }

        return $this->successResponse($conversation, 'AI conversation retrieved successfully');
    }

    /**
     * Update the specified AI conversation
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'sometimes|exists:leads,id',
            'property_id' => 'sometimes|exists:properties,id',
            'channel' => 'sometimes|in:sms,voice,email,chat,whatsapp',
            'external_id' => 'sometimes|string|max:255',
            'messages' => 'sometimes|array',
            'sentiment_score' => 'sometimes|integer|min:0|max:100',
            'urgency_score' => 'sometimes|integer|min:0|max:100',
            'motivation_score' => 'sometimes|integer|min:0|max:100',
            'qualification_score' => 'sometimes|integer|min:0|max:100',
            'extracted_data' => 'sometimes|array',
            'identified_pain_points' => 'sometimes|array',
            'detected_keywords' => 'sometimes|array',
            'status' => 'sometimes|in:active,completed,transferred,failed',
            'transferred_to_human' => 'sometimes|boolean',
            'transfer_reason' => 'sometimes|string',
            'outcome' => 'sometimes|in:qualified_lead,appointment_scheduled,not_interested,callback_requested,transferred',
            'next_steps' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'aiconversation validation');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockUpdate($request, $id);
        }

        // Real implementation
        $conversation = AiConversation::where('user_id', auth()->id())->find($id);

        if (!$conversation) {
            return $this->notFoundResponse('AI conversation not found');
        }

        try {
            $validatedData = $validator->validated();
            $conversation->update($validatedData);
            $conversation->load(['user', 'lead', 'property']);
            
            return $this->successResponse($conversation, 'AI conversation updated successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'aiconversation operation', 'aiconversation');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('aiconversation operation', $e);
        }
    }

    /**
     * Remove the specified AI conversation
     */
    public function destroy($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockDestroy($id);
        }

        // Real implementation
        $conversation = AiConversation::where('user_id', auth()->id())->find($id);

        if (!$conversation) {
            return $this->notFoundResponse('AI conversation not found');
        }

        try {
            $conversation->delete();
            return $this->successResponse(null, 'AI conversation deleted successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'aiconversation operation', 'aiconversation');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('aiconversation operation', $e);
        }
    }

    // Mock handlers
    private function handleMockIndex(Request $request)
    {
        [$page, $perPage] = $this->getPaginationParams($request);
        $filters = array_merge(
            ['user_id' => 1], // Mock user ID
            $this->getFilterParams($request, ['channel', 'status', 'lead_id', 'property_id', 'outcome', 'transferred_to_human'])
        );

        $result = $this->mockDataService->getAiConversations($filters, $page, $perPage);
        
        return response()->json($this->formatPaginatedResponse($result, 'AI conversations retrieved successfully'));
    }

    private function handleMockStore(Request $request)
    {
        try {
            $conversationData = array_merge($request->all(), [
                'user_id' => 1, // Mock user ID
                'status' => $request->status ?? 'active',
                'transferred_to_human' => false,
                'created_at' => now()->format('Y-m-d\TH:i:s.u\Z'),
                'updated_at' => now()->format('Y-m-d\TH:i:s.u\Z'),
            ]);

            $conversation = $this->mockDataService->createAiConversation($conversationData);
            return $this->successResponse($conversation, 'AI conversation created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'aiconversation operation', 'aiconversation');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('aiconversation operation', $e);
        }
    }

    private function handleMockShow($id)
    {
        $conversation = $this->mockDataService->getAiConversation($id);

        if (!$conversation || $conversation['user_id'] !== 1) { // Mock user ID check
            return $this->notFoundResponse('AI conversation not found');
        }

        return $this->successResponse($conversation, 'AI conversation retrieved successfully');
    }

    private function handleMockUpdate(Request $request, $id)
    {
        $conversation = $this->mockDataService->getAiConversation($id);

        if (!$conversation || $conversation['user_id'] !== 1) { // Mock user ID check
            return $this->notFoundResponse('AI conversation not found');
        }

        $updatedConversation = $this->mockDataService->updateAiConversation($id, $request->all());

        return $this->successResponse($updatedConversation, 'AI conversation updated successfully');
    }

    private function handleMockDestroy($id)
    {
        $conversation = $this->mockDataService->getAiConversation($id);

        if (!$conversation || $conversation['user_id'] !== 1) { // Mock user ID check
            return $this->notFoundResponse('AI conversation not found');
        }

        $deleted = $this->mockDataService->deleteAiConversation($id);

        if (!$deleted) {
            return $this->notFoundResponse('AI conversation not found');
        }

        return $this->successResponse(null, 'AI conversation deleted successfully');
    }
}
