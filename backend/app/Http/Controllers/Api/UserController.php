<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\MockableController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use MockableController;

    public function __construct()
    {
        $this->initializeMockDataService();
    }

    /**
     * Get users based on user type:
     * - Super Admin: All users across all organizations with pagination and filtering
     * - Organization Admin: Users within their organization only with pagination and filtering
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            [$page, $perPage] = $this->getPaginationParams($request);
            $filters = $this->getFilterParams($request, ['organization_id', 'role', 'status']);

            // Super admin can see all users across organizations
            if ($user->isSuperAdmin()) {
                $query = User::with(['roles.permissions', 'organization']);

                // Apply filters
                if (isset($filters['organization_id'])) {
                    $query->where('organization_id', $filters['organization_id']);
                }

                if (isset($filters['role'])) {
                    $query->whereHas('roles', function($q) use ($filters) {
                        $q->where('name', $filters['role']);
                    });
                }

                if (isset($filters['status'])) {
                    $query->where('status', $filters['status']);
                }

                if (isset($filters['search'])) {
                    $query->where(function($q) use ($filters) {
                        $q->where('first_name', 'like', '%' . $filters['search'] . '%')
                          ->orWhere('last_name', 'like', '%' . $filters['search'] . '%')
                          ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                    });
                }

                $users = $query->paginate($perPage, ['*'], 'page', $page);

                $formattedUsers = $users->getCollection()->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'uuid' => $user->uuid,
                        'email' => $user->email,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'full_name' => $user->name,
                        'phone' => $user->phone,
                        'role' => $user->getRoleNames()->first(),
                        'status' => $user->status,
                        'is_active' => $user->is_active,
                        'is_verified' => $user->is_verified,
                        'organization' => [
                            'id' => $user->organization->id,
                            'name' => $user->organization->name,
                            'slug' => $user->organization->slug,
                            'subscription_status' => $user->organization->subscription_status,
                        ],
                        'roles' => $user->roles->map(function ($role) {
                            return [
                                'id' => $role->id,
                                'name' => $role->name,
                                'label' => $role->label,
                                'permissions' => $role->permissions->map(function ($permission) {
                                    return [
                                        'id' => $permission->id,
                                        'name' => $permission->name,
                                        'label' => $permission->label,
                                    ];
                                }),
                            ];
                        }),
                        'created_at' => $user->created_at->toISOString(),
                        'updated_at' => $user->updated_at->toISOString(),
                    ];
                });

                return $this->successResponse([
                    'users' => $formattedUsers,
                    'pagination' => [
                        'current_page' => $users->currentPage(),
                        'per_page' => $users->perPage(),
                        'total' => $users->total(),
                        'last_page' => $users->lastPage(),
                        'from' => $users->firstItem(),
                        'to' => $users->lastItem(),
                    ],
                    'filters_applied' => $filters,
                ], 'Users retrieved successfully');
            }

            // Organization admin sees only their organization's users
            $orgId = $user->organization_id;

            $query = User::with(['roles.permissions'])
                ->where('organization_id', $orgId);

            // Apply filters
            if (isset($filters['role'])) {
                $query->whereHas('roles', function($q) use ($filters) {
                    $q->where('name', $filters['role']);
                });
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['search'])) {
                $query->where(function($q) use ($filters) {
                    $q->where('first_name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('last_name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                });
            }

            $users = $query->paginate($perPage, ['*'], 'page', $page);

            $formattedUsers = $users->getCollection()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->name,
                    'phone' => $user->phone,
                    'role' => $user->getRoleNames()->first(),
                    'status' => $user->status,
                    'is_active' => $user->is_active,
                    'is_verified' => $user->is_verified,
                    'roles' => $user->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'label' => $role->label,
                            'permissions' => $role->permissions->map(function ($permission) {
                                return [
                                    'id' => $permission->id,
                                    'name' => $permission->name,
                                    'label' => $permission->label,
                                ];
                            }),
                        ];
                    }),
                    'created_at' => $user->created_at->toISOString(),
                    'updated_at' => $user->updated_at->toISOString(),
                ];
            });

            return $this->successResponse([
                'users' => $formattedUsers,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ],
                'filters_applied' => $filters,
            ], 'Organization users retrieved successfully');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('retrieving users', $e);
        }
    }

    /**
     * Get specific user details based on user type:
     * - Super Admin: Any user across organizations
     * - Organization Admin: Users within their organization only
     */
    public function show(Request $request, User $targetUser)
    {
        try {
            $user = $request->user();

            // Super admin can see any user
            if ($user->isSuperAdmin()) {
                $targetUser->load(['roles.permissions', 'organization']);

                $userData = [
                    'id' => $targetUser->id,
                    'uuid' => $targetUser->uuid,
                    'email' => $targetUser->email,
                    'first_name' => $targetUser->first_name,
                    'last_name' => $targetUser->last_name,
                    'full_name' => $targetUser->name,
                    'phone' => $targetUser->phone,
                    'role' => $targetUser->getPrimaryRoleName(),
                    'status' => $targetUser->status,
                    'is_active' => $targetUser->is_active,
                    'is_verified' => $targetUser->is_verified,
                    'level' => $targetUser->level,
                    'points' => $targetUser->points,
                    'organization' => [
                        'id' => $targetUser->organization->id,
                        'name' => $targetUser->organization->name,
                        'slug' => $targetUser->organization->slug,
                        'subscription_status' => $targetUser->organization->subscription_status,
                    ],
                    'roles' => $targetUser->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'label' => $role->label,
                            'permissions' => $role->permissions->map(function ($permission) {
                                return [
                                    'id' => $permission->id,
                                    'name' => $permission->name,
                                    'label' => $permission->label,
                                ];
                            }),
                        ];
                    }),
                    'created_at' => $targetUser->created_at->toISOString(),
                    'updated_at' => $targetUser->updated_at->toISOString(),
                    'last_login_at' => $targetUser->last_login_at ? $targetUser->last_login_at->toISOString() : null,
                ];

                return $this->successResponse($userData, 'User details retrieved successfully');
            }

            // Organization admin can only see users from their organization
            if ($targetUser->organization_id !== $user->organization_id) {
                return $this->forbiddenResponse('access this user', 'same organization');
            }

            $targetUser->load(['roles.permissions']);

            $userData = [
                'id' => $targetUser->id,
                'uuid' => $targetUser->uuid,
                'email' => $targetUser->email,
                'first_name' => $targetUser->first_name,
                'last_name' => $targetUser->last_name,
                'full_name' => $targetUser->name,
                'phone' => $targetUser->phone,
                'role' => $targetUser->getPrimaryRoleName(),
                'status' => $targetUser->status,
                'is_active' => $targetUser->is_active,
                'is_verified' => $targetUser->is_verified,
                'level' => $targetUser->level,
                'points' => $targetUser->points,
                'roles' => $targetUser->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'label' => $role->label,
                        'permissions' => $role->permissions->map(function ($permission) {
                            return [
                                'id' => $permission->id,
                                'name' => $permission->name,
                                'label' => $permission->label,
                            ];
                        }),
                    ];
                }),
                'created_at' => $targetUser->created_at->toISOString(),
                'updated_at' => $targetUser->updated_at->toISOString(),
                'last_login_at' => $targetUser->last_login_at ? $targetUser->last_login_at->toISOString() : null,
            ];

            return $this->successResponse($userData, 'User details retrieved successfully');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('retrieving user details', $e);
        }
    }

    /**
     * Update user roles based on user type:
     * - Super Admin: Can update roles for any user (cross-organization)
     * - Organization Admin: Can update roles for users within their organization only
     */
    public function updateRoles(Request $request, User $targetUser)
    {
        $validator = Validator::make($request->all(), [
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'updating user roles');
        }

        try {
            $user = $request->user();

            // Super admin can update any user's roles
            if ($user->isSuperAdmin()) {
                $roleIds = Role::whereNull('organization_id')
                    ->whereIn('name', $request->roles)
                    ->pluck('id');

                if ($roleIds->count() !== count($request->roles)) {
                    return $this->businessLogicErrorResponse(
                        'Some roles do not exist',
                        'INVALID_ROLES',
                        ['requested_roles' => $request->roles],
                        ['Ensure all roles exist', 'Check role names for typos']
                    );
                }

                $targetUser->roles()->sync($roleIds);
                $targetUser->load(['roles.permissions']);

                $updatedRoles = $targetUser->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'label' => $role->label,
                        'permissions' => $role->permissions->map(function ($permission) {
                            return [
                                'id' => $permission->id,
                                'name' => $permission->name,
                                'label' => $permission->label,
                            ];
                        }),
                    ];
                });

                return $this->successResponse([
                    'user_id' => $targetUser->id,
                    'user_email' => $targetUser->email,
                    'organization' => [
                        'id' => $targetUser->organization->id,
                        'name' => $targetUser->organization->name,
                    ],
                    'roles' => $updatedRoles,
                    'updated_at' => now()->toISOString(),
                ], 'User roles updated successfully');
            }

            // Organization admin can only update users from their organization
            if ($targetUser->organization_id !== $user->organization_id) {
                return $this->forbiddenResponse('update this user', 'same organization');
            }

            $roleIds = Role::whereNull('organization_id')
                ->whereIn('name', $request->roles)
                ->pluck('id');

            if ($roleIds->count() !== count($request->roles)) {
                return $this->businessLogicErrorResponse(
                    'Some roles do not exist',
                    'INVALID_ROLES',
                    ['requested_roles' => $request->roles],
                    ['Ensure all roles exist', 'Check role names for typos']
                );
            }

            $targetUser->roles()->sync($roleIds);
            $targetUser->load(['roles.permissions']);

            $updatedRoles = $targetUser->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'label' => $role->label,
                    'permissions' => $role->permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'label' => $permission->label,
                        ];
                    }),
                ];
            });

            return $this->successResponse([
                'user_id' => $targetUser->id,
                'user_email' => $targetUser->email,
                'roles' => $updatedRoles,
                'updated_at' => now()->toISOString(),
            ], 'User roles updated successfully');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('updating user roles', $e);
        }
    }

    /**
     * Update user status based on user type:
     * - Super Admin: Can update status for any user
     * - Organization Admin: Can update status for users within their organization only
     */
    public function updateStatus(Request $request, User $targetUser)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'updating user status');
        }

        try {
            $user = $request->user();

            // Super admin can update any user's status
            if ($user->isSuperAdmin()) {
                $newStatus = $request->status;
                $isActive = $newStatus === 'active';

                $targetUser->update([
                    'status' => $newStatus,
                    'is_active' => $isActive,
                ]);

                return $this->successResponse($targetUser, 'User status updated successfully');
            }

            // Organization admin can only update users from their organization
            if ($targetUser->organization_id !== $user->organization_id) {
                return $this->forbiddenResponse('update this user', 'same organization');
            }

            $newStatus = $request->status;
            $isActive = $newStatus === 'active';

            $targetUser->update([
                'status' => $newStatus,
                'is_active' => $isActive,
            ]);

            return $this->successResponse($targetUser, 'User status updated successfully');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('updating user status', $e);
        }
    }

    /**
     * Update user profile information.
     * - A user can update their own profile.
     * - An organization admin can update any user in their organization.
     * - A super admin can update any user.
     */
    public function updateProfile(Request $request, User $targetUser)
    {
        $user = $request->user();

        // Authorization: Check if the user is allowed to update the target user's profile
        if (!$user->isSuperAdmin() && $user->id !== $targetUser->id && $user->organization_id !== $targetUser->organization_id) {
            return $this->forbiddenResponse('update this user\'s profile');
        }
        
        if (!$user->isSuperAdmin() && $user->id !== $targetUser->id && !$user->hasRole('admin')) {
            return $this->forbiddenResponse('update this user\'s profile');
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'updating user profile');
        }

        try {
            $dataToUpdate = $request->only(['first_name', 'last_name', 'phone']);

            $targetUser->update($dataToUpdate);

            return $this->successResponse($targetUser, 'User profile updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('updating user profile', $e);
        }
    }
}
