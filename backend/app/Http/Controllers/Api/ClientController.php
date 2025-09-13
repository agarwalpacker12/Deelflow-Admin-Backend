<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Traits\MockableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    use MockableController;

    public function __construct()
    {
        $this->initializeMockDataService();
    }

    /**
     * Display a listing of clients with filtering and pagination
     */
    public function index(Request $request)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockIndex($request);
        }

        // Real implementation
        [$page, $perPage] = $this->getPaginationParams($request);
        
        $query = Client::where('user_id', auth()->id());

        // Apply filters
        if ($request->has('client_type')) {
            $query->where('client_type', $request->client_type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('needs_followup') && $request->boolean('needs_followup')) {
            $query->needingFollowup();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $clients = $query->with(['leads'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return $this->successResponse([
            'data' => $clients->items(),
            'meta' => [
                'current_page' => $clients->currentPage(),
                'per_page' => $clients->perPage(),
                'total' => $clients->total(),
                'last_page' => $clients->lastPage(),
                'from' => $clients->firstItem(),
                'to' => $clients->lastItem(),
            ]
        ], 'Clients retrieved successfully');
    }

    /**
     * Store a newly created client
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_type' => 'required|in:seller,buyer',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'zip' => 'nullable|string|max:10',
            'occupation' => 'nullable|string|max:200',
            'employer' => 'nullable|string|max:200',
            'annual_income' => 'nullable|numeric|min:0',
            'net_worth' => 'nullable|numeric|min:0',
            'liquid_assets' => 'nullable|numeric|min:0',
            'credit_score' => 'nullable|integer|min:300|max:850',
            'has_financing_preapproval' => 'nullable|boolean',
            'financing_amount' => 'nullable|numeric|min:0',
            'investment_criteria' => 'nullable|array',
            'investment_goals' => 'nullable|array',
            'investment_experience' => 'nullable|in:beginner,intermediate,expert',
            'owned_properties' => 'nullable|array',
            'selling_motivation' => 'nullable|string|max:200',
            'selling_timeline' => 'nullable|string|max:100',
            'preferred_contact_method' => 'nullable|in:phone,email,text',
            'best_time_to_call' => 'nullable|string|max:100',
            'source' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'next_followup_at' => 'nullable|date|after:now',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'client validation');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockStore($request);
        }

        // Real implementation
        try {
            $validatedData = $validator->validated();
            $client = Client::create(array_merge($validatedData, [
                'user_id' => auth()->id(),
                'uuid' => \Illuminate\Support\Str::uuid(),
                'status' => 'prospect',
                'relationship_score' => 0,
            ]));

            $client->load(['leads']);

            return $this->successResponse($client, 'Client created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'client operation', 'client');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('client operation', $e);
        }
    }

    /**
     * Display the specified client
     */
    public function show($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockShow($id);
        }

        // Real implementation
        $client = Client::with(['leads', 'user'])
            ->where('user_id', auth()->id())
            ->find($id);

        if (!$client) {
            return $this->notFoundResponse('Client not found');
        }

        return $this->successResponse($client, 'Client retrieved successfully');
    }

    /**
     * Update the specified client
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'client_type' => 'sometimes|in:seller,buyer',
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email',
            'phone' => 'sometimes|string|max:20',
            'alternate_phone' => 'sometimes|string|max:20',
            'date_of_birth' => 'sometimes|date',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:2',
            'zip' => 'sometimes|string|max:10',
            'occupation' => 'sometimes|string|max:200',
            'employer' => 'sometimes|string|max:200',
            'annual_income' => 'sometimes|numeric|min:0',
            'net_worth' => 'sometimes|numeric|min:0',
            'liquid_assets' => 'sometimes|numeric|min:0',
            'credit_score' => 'sometimes|integer|min:300|max:850',
            'has_financing_preapproval' => 'sometimes|boolean',
            'financing_amount' => 'sometimes|numeric|min:0',
            'investment_criteria' => 'sometimes|array',
            'investment_goals' => 'sometimes|array',
            'investment_experience' => 'sometimes|in:beginner,intermediate,expert',
            'owned_properties' => 'sometimes|array',
            'selling_motivation' => 'sometimes|string|max:200',
            'selling_timeline' => 'sometimes|string|max:100',
            'preferred_contact_method' => 'sometimes|in:phone,email,text',
            'best_time_to_call' => 'sometimes|string|max:100',
            'status' => 'sometimes|in:prospect,active,closed,inactive',
            'source' => 'sometimes|string|max:100',
            'notes' => 'sometimes|string',
            'relationship_score' => 'sometimes|integer|min:0|max:100',
            'last_contact_at' => 'sometimes|date',
            'next_followup_at' => 'sometimes|date',
            'tags' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'client validation');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockUpdate($request, $id);
        }

        // Real implementation
        $client = Client::where('user_id', auth()->id())->find($id);

        if (!$client) {
            return $this->notFoundResponse('Client not found');
        }

        try {
            $validatedData = $validator->validated();
            $client->update($validatedData);
            $client->load(['leads']);
            
            return $this->successResponse($client, 'Client updated successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'client operation', 'client');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('client operation', $e);
        }
    }

    /**
     * Remove the specified client
     */
    public function destroy($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockDestroy($id);
        }

        // Real implementation
        $client = Client::where('user_id', auth()->id())->find($id);

        if (!$client) {
            return $this->notFoundResponse('Client not found');
        }

        try {
            $client->delete();
            return $this->successResponse(null, 'Client deleted successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'client operation', 'client');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('client operation', $e);
        }
    }

    // Mock handlers
    private function handleMockIndex(Request $request)
    {
        [$page, $perPage] = $this->getPaginationParams($request);
        $filters = array_merge(
            ['user_id' => 1], // Mock user ID
            $this->getFilterParams($request, ['client_type', 'status'])
        );

        $result = $this->mockDataService->getClients($filters, $page, $perPage);
        
        return response()->json($this->formatPaginatedResponse($result, 'Clients retrieved successfully'));
    }

    private function handleMockStore(Request $request)
    {
        try {
            $clientData = array_merge($request->all(), [
                'user_id' => 1, // Mock user ID
                'uuid' => \Illuminate\Support\Str::uuid(),
                'status' => 'prospect',
                'relationship_score' => 0,
                'created_at' => now()->format('Y-m-d\TH:i:s.u\Z'),
                'updated_at' => now()->format('Y-m-d\TH:i:s.u\Z'),
            ]);

            $client = $this->mockDataService->createClient($clientData);
            return $this->successResponse($client, 'Client created successfully', 201);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('client operation', $e);
        }
    }

    private function handleMockShow($id)
    {
        $client = $this->mockDataService->getClient($id);

        if (!$client || $client['user_id'] !== 1) { // Mock user ID check
            return $this->notFoundResponse('Client not found');
        }

        return $this->successResponse($client, 'Client retrieved successfully');
    }

    private function handleMockUpdate(Request $request, $id)
    {
        $client = $this->mockDataService->getClient($id);

        if (!$client || $client['user_id'] !== 1) { // Mock user ID check
            return $this->notFoundResponse('Client not found');
        }

        $updatedClient = $this->mockDataService->updateClient($id, $request->all());

        return $this->successResponse($updatedClient, 'Client updated successfully');
    }

    private function handleMockDestroy($id)
    {
        $client = $this->mockDataService->getClient($id);

        if (!$client || $client['user_id'] !== 1) { // Mock user ID check
            return $this->notFoundResponse('Client not found');
        }

        $deleted = $this->mockDataService->deleteClient($id);

        if (!$deleted) {
            return $this->notFoundResponse('Client not found');
        }

        return $this->successResponse(null, 'Client deleted successfully');
    }
}
