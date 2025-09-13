<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertySave;
use App\Traits\MockableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PropertySaveController extends Controller
{
    use MockableController;

    public function __construct()
    {
        $this->initializeMockDataService();
    }

    /**
     * Display a listing of saved properties for the authenticated user
     */
    public function index(Request $request)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockIndex($request);
        }

        // Real implementation
        [$page, $perPage] = $this->getPaginationParams($request);
        
        $query = PropertySave::with(['property', 'user'])
            ->where('user_id', auth()->id());

        $saves = $query->paginate($perPage, ['*'], 'page', $page);

        return $this->successResponse([
            'data' => $saves->items(),
            'meta' => [
                'current_page' => $saves->currentPage(),
                'per_page' => $saves->perPage(),
                'total' => $saves->total(),
                'last_page' => $saves->lastPage(),
                'from' => $saves->firstItem(),
                'to' => $saves->lastItem(),
            ]
        ], 'Saved properties retrieved successfully');
    }

    /**
     * Save a property to user's favorites
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'propertysave validation');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockStore($request);
        }

        // Real implementation
        try {
            // Check if already saved
            $existingSave = PropertySave::where('user_id', auth()->id())
                ->where('property_id', $request->property_id)
                ->first();

            if ($existingSave) {
                return $this->errorResponse('Property is already saved', 409);
            }

            $save = PropertySave::create([
                'user_id' => auth()->id(),
                'property_id' => $request->property_id,
            ]);

            $save->load(['property', 'user']);

            return $this->successResponse($save, 'Property saved successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'propertysave operation', 'propertysave');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('propertysave operation', $e);
        }
    }

    /**
     * Display the specified saved property
     */
    public function show($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockShow($id);
        }

        // Real implementation
        $save = PropertySave::with(['property', 'user'])
            ->where('user_id', auth()->id())
            ->find($id);

        if (!$save) {
            return $this->notFoundResponse('Saved property not found');
        }

        return $this->successResponse($save, 'Saved property retrieved successfully');
    }

    /**
     * Remove the specified saved property
     */
    public function destroy($id)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockDestroy($id);
        }

        // Real implementation
        $save = PropertySave::where('user_id', auth()->id())->find($id);

        if (!$save) {
            return $this->notFoundResponse('Saved property not found');
        }

        try {
            $save->delete();
            return $this->successResponse(null, 'Property removed from saved list successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'propertysave operation', 'propertysave');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('propertysave operation', $e);
        }
    }

    /**
     * Update method is not needed for PropertySave as it's a simple pivot table
     */
    public function update(Request $request, $id)
    {
        return $this->errorResponse('Update operation is not supported for saved properties', null, 405);
    }

    // Mock handlers
    private function handleMockIndex(Request $request)
    {
        [$page, $perPage] = $this->getPaginationParams($request);
        $filters = ['user_id' => 1]; // Mock user ID

        $result = $this->mockDataService->getPropertySaves($filters, $page, $perPage);
        
        return response()->json($this->formatPaginatedResponse($result, 'Saved properties retrieved successfully'));
    }

    private function handleMockStore(Request $request)
    {
        try {
            // Check if already saved in mock data
            $existing = $this->mockDataService->getPropertySaveByUserAndProperty(1, $request->property_id);
            
            if ($existing) {
                return $this->errorResponse('Property is already saved', 409);
            }

            $saveData = [
                'user_id' => 1, // Mock user ID
                'property_id' => $request->property_id,
                'saved_at' => now()->format('Y-m-d\TH:i:s.u\Z'),
            ];

            $save = $this->mockDataService->createPropertySave($saveData);
            return $this->successResponse($save, 'Property saved successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'propertysave operation', 'propertysave');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('propertysave operation', $e);
        }
    }

    private function handleMockShow($id)
    {
        $save = $this->mockDataService->getPropertySave($id);

        if (!$save || $save['user_id'] !== 1) { // Mock user ID check
            return $this->notFoundResponse('Saved property not found');
        }

        return $this->successResponse($save, 'Saved property retrieved successfully');
    }

    private function handleMockDestroy($id)
    {
        $save = $this->mockDataService->getPropertySave($id);

        if (!$save || $save['user_id'] !== 1) { // Mock user ID check
            return $this->notFoundResponse('Saved property not found');
        }

        $deleted = $this->mockDataService->deletePropertySave($id);

        if (!$deleted) {
            return $this->notFoundResponse('Saved property not found');
        }

        return $this->successResponse(null, 'Property removed from saved list successfully');
    }
}
