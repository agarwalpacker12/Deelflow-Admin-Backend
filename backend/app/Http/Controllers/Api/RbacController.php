<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\MockableController;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Organization;
use Illuminate\Support\Facades\Validator;

class RbacController extends Controller
{
    use MockableController;

    public function __construct()
    {
        $this->initializeMockDataService();
    }

    /**
     * Get all global roles with their permissions.
     * Note: Roles are now global, not organization-specific.
     */
    public function getRoles(Request $request)
    {
        try {
            $user = $request->user();

            // Super admin can see all roles
            if ($user->isSuperAdmin()) {
                $roles = Role::with(['permissions', 'users'])->get();
            } else {
                // Regular users see all roles except super admin role
                $roles = Role::with('permissions')
                    ->where('name', '!=', 'super_admin')
                    ->get();
            }

            $formattedRoles = $roles->map(function ($role) use ($user) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'label' => $role->label,
                    'users_count' => $user->isSuperAdmin() ? $role->users->count() : null,
                    'permissions' => $role->permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'label' => $permission->label,
                        ];
                    }),
                    'created_at' => $role->created_at->toISOString(),
                    'updated_at' => $role->updated_at->toISOString(),
                ];
            });

            return $this->successResponse([
                'roles' => $formattedRoles,
                'total_roles' => $formattedRoles->count(),
            ], 'Roles retrieved successfully');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('retrieving roles', $e);
        }
    }

    /**
     * Get all global permissions with their associated roles (Super Admin only)
     */
    public function getPermissions(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->isSuperAdmin()) {
                return $this->forbiddenResponse('access permissions data', 'super admin');
            }

            $permissions = Permission::with('roles')->get();
            $allRoles = Role::all();

            $groupedPermissions = $permissions->groupBy('group')->map(function ($group, $groupName) use ($allRoles) {
                return [
                    'group' => $groupName,
                    'permissions' => $group->map(function ($permission) use ($allRoles) {
                        $assignedRoleIds = $permission->roles->pluck('id')->toArray();
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'label' => $permission->label,
                            'roles' => $allRoles->map(function ($role) use ($assignedRoleIds) {
                                return [
                                    'id' => $role->id,
                                    'name' => $role->name,
                                    'label' => $role->label,
                                    'enabled' => in_array($role->id, $assignedRoleIds),
                                ];
                            }),
                            'created_at' => $permission->created_at->toISOString(),
                            'updated_at' => $permission->updated_at->toISOString(),
                        ];
                    }),
                ];
            });

            return $this->successResponse([
                'permission_groups' => $groupedPermissions->values(),
                'total_permissions' => $permissions->count(),
            ], 'Permissions retrieved successfully');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('retrieving permissions', $e);
        }
    }

    /**
     * Update role permissions (Super Admin only)
     * Note: Permissions are now global, not organization-specific.
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'updating role permissions');
        }

        try {
            $user = $request->user();

            // Only super admin can access this endpoint
            if (!$user->isSuperAdmin()) {
                return $this->forbiddenResponse('update role permissions', 'super admin');
            }

            // Get permission IDs for the requested permissions (now global)
            $permissionIds = Permission::whereIn('name', $request->permissions)
                ->pluck('id');

            if ($permissionIds->count() !== count($request->permissions)) {
                return $this->businessLogicErrorResponse(
                    'Some permissions do not exist',
                    'INVALID_PERMISSIONS',
                    ['requested_permissions' => $request->permissions],
                    ['Ensure all permissions exist', 'Check permission names for typos']
                );
            }

            // Update role permissions
            $role->permissions()->sync($permissionIds);
            $role->load(['permissions']);

            $updatedPermissions = $role->permissions->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'label' => $permission->label,
                ];
            });

            return $this->successResponse([
                'role_id' => $role->id,
                'role_name' => $role->name,
                'role_label' => $role->label,
                'permissions' => $updatedPermissions,
                'updated_at' => now()->toISOString(),
            ], 'Role permissions updated successfully');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('updating role permissions', $e);
        }
    }
}
