<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Traits\MockableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    use MockableController;

    public function __construct()
    {
        $this->initializeMockDataService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockIndex($request);
        }

        // Real implementation
        $user = $request->user();
        
        // Super admin can see all organizations
        if ($user->isSuperAdmin()) {
            [$page, $perPage] = $this->getPaginationParams($request);
            $filters = $this->getFilterParams($request, ['subscription_status']);

            $query = Organization::withCount(['users', 'roles', 'permissions']);

            if (isset($filters['subscription_status'])) {
                $query->where('subscription_status', $filters['subscription_status']);
            }

            if (isset($filters['search'])) {
                $query->where('name', 'like', '%' . $filters['search'] . '%');
            }

            $organizations = $query->paginate($perPage, ['*'], 'page', $page);

            $formattedOrganizations = $organizations->getCollection()->map(function ($organization) {
                return [
                    'id' => $organization->id,
                    'uuid' => $organization->uuid,
                    'name' => $organization->name,
                    'slug' => $organization->slug,
                    'subscription_status' => $organization->subscription_status,
                    'industry' => $organization->industry,
                    'organization_size' => $organization->organization_size,
                    'business_email' => $organization->business_email,
                    'business_phone' => $organization->business_phone,
                    'website' => $organization->website,
                    'users_count' => $organization->users_count,
                    'roles_count' => $organization->roles_count,
                    'permissions_count' => $organization->permissions_count,
                    'created_at' => $organization->created_at->toISOString(),
                    'updated_at' => $organization->updated_at->toISOString(),
                ];
            });

            return $this->successResponse([
                'organizations' => $formattedOrganizations,
                'pagination' => [
                    'current_page' => $organizations->currentPage(),
                    'per_page' => $organizations->perPage(),
                    'total' => $organizations->total(),
                    'last_page' => $organizations->lastPage(),
                    'from' => $organizations->firstItem(),
                    'to' => $organizations->lastItem(),
                ],
                'filters_applied' => $filters,
            ], 'Organizations retrieved successfully');
        }

        // Regular users see only their organization
        if (!$user->organization) {
            return $this->notFoundResponse('organization');
        }

        return $this->successResponse($user->organization, 'Organization retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:organizations,name',
            'industry' => 'nullable|string|max:255',
            'organization_size' => 'nullable|string|max:255',
            'business_email' => 'nullable|email|max:255',
            'business_phone' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'support_email' => 'nullable|email|max:255',
            'street_address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state_province' => 'nullable|string|max:255',
            'zip_postal_code' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'organization creation');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockStore($request);
        }

        // Real implementation
        try {
            $validatedData = $validator->validated();
            $validatedData['uuid'] = Str::uuid();
            $validatedData['slug'] = Str::slug($validatedData['name']);
            $validatedData['subscription_status'] = 'new';

            $organization = Organization::create($validatedData);

            return $this->successResponse($organization, 'Organization created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'organization creation', 'organization');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('organization creation', $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockShow($organization->id);
        }

        // Real implementation - check if user belongs to this organization
        $user = auth()->user();
        if ($user->organization_id !== $organization->id) {
            return $this->forbiddenResponse('access this organization');
        }

        return $this->successResponse($organization, 'Organization retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organization $organization)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:organizations,name,' . $organization->id,
            'industry' => 'nullable|string|max:255',
            'organization_size' => 'nullable|string|max:255',
            'business_email' => 'nullable|email|max:255',
            'business_phone' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'support_email' => 'nullable|email|max:255',
            'street_address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state_province' => 'nullable|string|max:255',
            'zip_postal_code' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'organization update');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockUpdate($request, $organization->id);
        }

        // Real implementation - check if user belongs to this organization and has admin role
        $user = auth()->user();
        if ($user->organization_id !== $organization->id) {
            return $this->forbiddenResponse('access this organization');
        }

        if (!$user->hasRole('admin')) {
            return $this->forbiddenResponse('update organization details', 'admin');
        }

        try {
            $validatedData = $validator->validated();

            if (isset($validatedData['name'])) {
                $validatedData['slug'] = Str::slug($validatedData['name']);
            }

            $organization->update($validatedData);

            return $this->successResponse($organization, 'Organization updated successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'organization update', 'organization');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('organization update', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockDestroy($organization->id);
        }

        // Real implementation - check if user belongs to this organization and has admin role
        $user = auth()->user();
        if ($user->organization_id !== $organization->id) {
            return $this->forbiddenResponse('access this organization');
        }

        if (!$user->hasRole('admin')) {
            return $this->forbiddenResponse('delete this organization', 'admin');
        }

        $adminCount = $organization->users()->whereHas('roles', function($q) {
            $q->where('name', 'admin');
        })->count();

        if ($adminCount > 0) {
            return $this->businessLogicErrorResponse(
                'Cannot delete an organization with admin users.',
                'ORGANIZATION_HAS_ADMIN_USERS',
                [
                    'admin_count' => $adminCount,
                    'organization_id' => $organization->id
                ],
                [
                    'Remove or transfer admin users before deleting the organization',
                    'Consider deactivating the organization instead of deleting it'
                ]
            );
        }

        try {
            $organization->delete();
            return $this->successResponse(null, 'Organization deleted successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'organization deletion', 'organization');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('organization deletion', $e);
        }
    }

    /**
     * Get the subscription status of the current user's organization
     */
    public function getStatus(Request $request)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockGetStatus($request);
        }

        // Real implementation
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            return $this->successResponse(['status' => 'super_admin'], 'Status retrieved successfully');
        }

        if (!$user->organization) {
            return $this->notFoundResponse('organization');
        }

        return $this->successResponse([
            'status' => $user->organization->subscription_status,
            'organization_id' => $user->organization->id,
            'organization_name' => $user->organization->name
        ], 'Organization status retrieved successfully');
    }

    /**
     * Update the subscription status of an organization
     */
    public function updateSubscriptionStatus(Request $request, Organization $organization)
    {
        $validator = Validator::make($request->all(), [
            'subscription_status' => 'required|string|in:new,active,suspended,waiting',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'subscription status update');
        }

        if ($this->isMockEnabled()) {
            return $this->handleMockUpdateSubscriptionStatus($request, $organization->id);
        }

        // Real implementation - check permissions
        $user = auth()->user();
        if (!$user->hasRole('super_admin') && $user->organization_id !== $organization->id) {
            return $this->forbiddenResponse('update subscription status for this organization');
        }

        if (!$user->hasRole('super_admin') && !$user->hasRole('admin')) {
            return $this->forbiddenResponse('update subscription status', 'admin');
        }

        try {
            $organization->update($validator->validated());

            return $this->successResponse($organization, 'Subscription status updated successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'subscription status update', 'organization');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('subscription status update', $e);
        }
    }

    /**
     * Remove a user from an organization
     */
    public function removeUser(Request $request, Organization $organization, User $user)
    {
        if ($this->isMockEnabled()) {
            return $this->handleMockRemoveUser($request, $organization->id, $user->id);
        }

        // Real implementation - check permissions
        $currentUser = auth()->user();
        if ($currentUser->organization_id !== $organization->id) {
            return $this->forbiddenResponse('access this organization');
        }

        if (!$currentUser->hasRole('admin')) {
            return $this->forbiddenResponse('remove users from the organization', 'admin');
        }

        if ($user->organization_id !== $organization->id) {
            return $this->notFoundResponse('user in this organization', $user->id);
        }

        $adminCount = $organization->users()->whereHas('roles', function($q) {
            $q->where('name', 'admin');
        })->count();

        if ($user->hasRole('admin') && $adminCount === 1) {
            return $this->businessLogicErrorResponse(
                'Cannot remove the only admin user from an organization.',
                'CANNOT_REMOVE_LAST_ADMIN',
                [
                    'user_id' => $user->id,
                    'organization_id' => $organization->id,
                    'admin_count' => 1
                ],
                [
                    'Assign admin role to another user before removing this admin',
                    'Consider deactivating the user instead of removing them'
                ]
            );
        }

        try {
            $user->update(['is_active' => false, 'status' => 'cancelled']);

            return $this->successResponse(null, 'User removed from organization successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'user removal', 'user');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('user removal', $e);
        }
    }

    // Mock handlers
    private function handleMockIndex(Request $request)
    {
        try {
            $organization = $this->mockDataService->getOrganization(1); // Mock organization ID
            return $this->successResponse($organization, 'Organization retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('organization retrieval', $e);
        }
    }

    private function handleMockStore(Request $request)
    {
        try {
            $organizationData = array_merge($request->all(), [
                'uuid' => Str::uuid(),
                'slug' => Str::slug($request->name),
                'subscription_status' => 'new',
                'created_at' => now()->format('Y-m-d\TH:i:s.u\Z'),
                'updated_at' => now()->format('Y-m-d\TH:i:s.u\Z'),
            ]);

            $organization = $this->mockDataService->createOrganization($organizationData);
            return $this->successResponse($organization, 'Organization created successfully', 201);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('organization creation', $e);
        }
    }

    private function handleMockShow($id)
    {
        $organization = $this->mockDataService->getOrganization($id);

        if (!$organization) {
            return $this->notFoundResponse('organization', $id);
        }

        return $this->successResponse($organization, 'Organization retrieved successfully');
    }

    private function handleMockUpdate(Request $request, $id)
    {
        $organization = $this->mockDataService->updateOrganization($id, $request->all());

        if (!$organization) {
            return $this->notFoundResponse('organization', $id);
        }

        return $this->successResponse($organization, 'Organization updated successfully');
    }

    private function handleMockDestroy($id)
    {
        $deleted = $this->mockDataService->deleteOrganization($id);

        if (!$deleted) {
            return $this->notFoundResponse('organization', $id);
        }

        return $this->successResponse(null, 'Organization deleted successfully');
    }

    private function handleMockGetStatus(Request $request)
    {
        return $this->successResponse([
            'status' => 'active',
            'organization_id' => 1,
            'organization_name' => 'Mock Organization'
        ], 'Organization status retrieved successfully');
    }

    private function handleMockUpdateSubscriptionStatus(Request $request, $id)
    {
        $organization = $this->mockDataService->updateOrganization($id, $request->all());

        if (!$organization) {
            return $this->notFoundResponse('organization', $id);
        }

        return $this->successResponse($organization, 'Subscription status updated successfully');
    }

    private function handleMockRemoveUser(Request $request, $organizationId, $userId)
    {
        // Mock implementation
        return $this->successResponse(null, 'User removed from organization successfully');
    }

    private function handleMockUpdateUserStatus(Request $request, $organizationId, $userId)
    {
        // Mock implementation
        $mockUser = [
            'id' => $userId,
            'status' => $request->status,
            'is_active' => $request->status === 'active',
            'updated_at' => now()->format('Y-m-d\TH:i:s.u\Z')
        ];

        return $this->successResponse($mockUser, 'User status updated successfully');
    }
}
