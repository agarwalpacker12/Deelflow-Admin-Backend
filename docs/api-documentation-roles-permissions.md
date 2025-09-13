# Role and Permission Management API Documentation

This document outlines all the role and permission management APIs available in the application.

## Overview

The application implements a **global role and permission management system**:

- **Global Roles**: Roles are shared across all organizations and are not organization-specific
- **Global Permissions**: Permissions are shared across all organizations and are not organization-specific
- **Simplified Management**: Single set of roles and permissions for the entire application

## Authentication & Authorization

All endpoints require authentication via Sanctum token in the Authorization header:
```
Authorization: Bearer {token}
```

### Access Control
- **Super Admin**: Can view all roles with user counts and manage role permissions
- **Regular Users**: Can view all roles (except super_admin role) without user counts
- Access is controlled by checking if the user has super admin privileges

## RBAC (Role-Based Access Control) Endpoints

### 1. Get Roles

**GET** `/api/rbac/roles`

Retrieves all global roles with their assigned permissions based on user type:
- **Super Admin**: Returns all roles with their permissions and user counts
- **Regular Users**: Returns all roles except super admin role with their permissions (no user counts)

**Response:**

**For Super Admin:**
```json
{
  "status": "success",
  "message": "Roles retrieved successfully",
  "data": {
    "roles": [
      {
        "id": 1,
        "name": "admin",
        "label": "Administrator",
        "users_count": 5,
        "permissions": [
          {
            "id": 1,
            "name": "manage_roles",
            "label": "Manage Roles"
          },
          {
            "id": 2,
            "name": "manage_client",
            "label": "Manage Clients"
          },
          {
            "id": 3,
            "name": "manage_campaign",
            "label": "Manage Campaigns"
          },
          {
            "id": 4,
            "name": "manage_org",
            "label": "Manage Organization"
          },
          {
            "id": 5,
            "name": "manage_lead",
            "label": "Manage Leads"
          },
          {
            "id": 6,
            "name": "manage_properties",
            "label": "Manage Properties"
          }
        ],
        "created_at": "2025-08-11T09:00:00.000000Z",
        "updated_at": "2025-08-11T09:00:00.000000Z"
      },
      {
        "id": 2,
        "name": "staff",
        "label": "Staff Member",
        "users_count": 12,
        "permissions": [
          {
            "id": 5,
            "name": "manage_lead",
            "label": "Manage Leads"
          },
          {
            "id": 6,
            "name": "manage_properties",
            "label": "Manage Properties"
          },
          {
            "id": 3,
            "name": "manage_campaign",
            "label": "Manage Campaigns"
          }
        ],
        "created_at": "2025-08-11T09:00:00.000000Z",
        "updated_at": "2025-08-11T09:00:00.000000Z"
      }
    ],
    "total_roles": 2
  }
}
```

**For Regular Users:**
```json
{
  "status": "success",
  "message": "Roles retrieved successfully",
  "data": {
    "roles": [
      {
        "id": 1,
        "name": "admin",
        "label": "Administrator",
        "users_count": null,
        "permissions": [
          {
            "id": 1,
            "name": "manage_roles",
            "label": "Manage Roles"
          },
          {
            "id": 2,
            "name": "manage_client",
            "label": "Manage Clients"
          },
          {
            "id": 3,
            "name": "manage_campaign",
            "label": "Manage Campaigns"
          },
          {
            "id": 4,
            "name": "manage_org",
            "label": "Manage Organization"
          },
          {
            "id": 5,
            "name": "manage_lead",
            "label": "Manage Leads"
          },
          {
            "id": 6,
            "name": "manage_properties",
            "label": "Manage Properties"
          }
        ],
        "created_at": "2025-08-11T09:00:00.000000Z",
        "updated_at": "2025-08-11T09:00:00.000000Z"
      },
      {
        "id": 2,
        "name": "staff",
        "label": "Staff Member",
        "users_count": null,
        "permissions": [
          {
            "id": 5,
            "name": "manage_lead",
            "label": "Manage Leads"
          },
          {
            "id": 6,
            "name": "manage_properties",
            "label": "Manage Properties"
          },
          {
            "id": 3,
            "name": "manage_campaign",
            "label": "Manage Campaigns"
          }
        ],
        "created_at": "2025-08-11T09:00:00.000000Z",
        "updated_at": "2025-08-11T09:00:00.000000Z"
      }
    ],
    "total_roles": 2
  }
}
```

**Note:** Regular user response excludes any `super_admin` roles and their permissions for security reasons, and `users_count` is set to `null`.

### 2. Get Permissions (Super Admin Only)

**GET** `/api/rbac/permissions`

Retrieves all global permissions, grouped by the `group` attribute. This endpoint is only accessible by super admin users.

**Access:** Super Admin only

**Response:**
```json
{
  "success": true,
  "status_code": 200,
  "message": "Permissions retrieved successfully",
  "data": {
    "permission_groups": [
      {
        "group": "User Management",
        "permissions": [
          {
            "id": 1,
            "name": "create_users",
            "label": "Create Users",
            "roles": [
              {
                "id": 1,
                "name": "super_admin",
                "label": "Super Administrator",
                "enabled": true
              },
              {
                "id": 2,
                "name": "organization_admin",
                "label": "Organization Administrator",
                "enabled": true
              }
            ],
            "created_at": "2025-08-12T04:00:00.000000Z",
            "updated_at": "2025-08-12T04:00:00.000000Z"
          }
        ]
      },
      {
        "group": "Billing",
        "permissions": [
          {
            "id": 2,
            "name": "manage_billing",
            "label": "Manage Billing",
            "roles": [
              {
                "id": 1,
                "name": "super_admin",
                "label": "Super Administrator",
                "enabled": true
              }
            ],
            "created_at": "2025-08-12T04:00:00.000000Z",
            "updated_at": "2025-08-12T04:00:00.000000Z"
          }
        ]
      }
    ],
    "total_permissions": 2
  }
}
```

**Error Response (Non-Super Admin):**
```json
{
  "status": "error",
  "message": "Access denied. You need super admin privileges to access permissions data.",
  "error_code": "FORBIDDEN"
}
```

### 3. Update Role Permissions (Super Admin Only)

**PUT** `/api/rbac/roles/{role}`

Updates the permissions assigned to a specific global role. This endpoint is only accessible by super admin users.

**Access:** Super Admin only

**Request Body:**
```json
{
  "permissions": ["manage_roles", "manage_client", "manage_campaign"]
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Role permissions updated successfully",
  "data": {
    "role_id": 1,
    "role_name": "admin",
    "role_label": "Administrator",
    "permissions": [
      {
        "id": 1,
        "name": "manage_roles",
        "label": "Manage Roles"
      },
      {
        "id": 2,
        "name": "manage_client",
        "label": "Manage Clients"
      },
      {
        "id": 3,
        "name": "manage_campaign",
        "label": "Manage Campaigns"
      }
    ],
    "updated_at": "2025-08-11T09:00:00.000000Z"
  }
}
```

**Error Response (Non-Super Admin):**
```json
{
  "status": "error",
  "message": "Access denied. You need super admin privileges to update role permissions.",
  "error_code": "FORBIDDEN"
}
```

**Error Response (Invalid Permissions):**
```json
{
  "status": "error",
  "message": "Some permissions do not exist",
  "error_code": "INVALID_PERMISSIONS",
  "details": {
    "requested_permissions": ["invalid_permission"]
  },
  "suggestions": [
    "Ensure all permissions exist",
    "Check permission names for typos"
  ]
}
```

**Validation Error Response:**
```json
{
  "status": "error",
  "message": "Validation failed for updating role permissions",
  "error_code": "VALIDATION_ERROR",
  "errors": [
    "The permissions field is required.",
    "The permissions.0 field must be a string."
  ]
}
```

## Global Roles and Permissions

### Default Global Roles
- **admin**: Full access to all permissions across the application
- **staff**: Limited access (manage_lead, manage_properties, manage_campaign)

### Default Global Permissions
- `manage_roles`: Manage Roles and Permissions
- `manage_client`: Manage Clients
- `manage_campaign`: Manage Campaigns
- `manage_org`: Manage Organization
- `manage_lead`: Manage Leads
- `manage_properties`: Manage Properties

## Key Features

### Global System Benefits
1. **Simplified Management**: Single set of roles and permissions for the entire application
2. **Consistency**: Same roles and permissions available across all organizations
3. **Easier Maintenance**: No need to manage organization-specific roles/permissions
4. **Better Performance**: No organization-scoped queries required
5. **Cleaner Architecture**: Simplified codebase without complex scoping logic

### Role Assignment
- Users can be assigned global roles that work across the entire application
- Role assignments are managed through the user management endpoints
- Users can have multiple roles assigned simultaneously

## Error Responses

### Validation Errors
```json
{
  "status": "error",
  "message": "Validation failed for updating role permissions",
  "error_code": "VALIDATION_ERROR",
  "errors": [
    "The permissions field is required.",
    "The permissions.0 field must be a string."
  ]
}
```

### Authorization Errors
```json
{
  "status": "error",
  "message": "Access denied. Super admin privileges required.",
  "error_code": "FORBIDDEN"
}
```

### Business Logic Errors
```json
{
  "status": "error",
  "message": "Some permissions do not exist",
  "error_code": "INVALID_PERMISSIONS",
  "details": {
    "requested_permissions": ["invalid_permission"]
  },
  "suggestions": [
    "Ensure all permissions exist",
    "Check permission names for typos"
  ]
}
```

## Security Features

1. **Super Admin Protection**: Super admin access is restricted to configured users
2. **Role-based Access**: Different access levels for super admin vs regular users
3. **Permission Validation**: All role and permission assignments are validated
4. **Global Consistency**: Unified permission system across the application

## Rate Limiting

All endpoints are subject to rate limiting:
- Authenticated users: 1000 requests per hour
- Unauthenticated users: 100 requests per hour

Rate limit headers are included in responses:
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Remaining requests
- `X-RateLimit-Reset`: Reset timestamp

## Migration Notes

**Important:** This system has been migrated from organization-specific roles and permissions to a global system. Key changes:

1. **Database Schema**: Removed `organization_id` columns from `roles` and `permissions` tables
2. **Unique Constraints**: Changed from organization-scoped to global unique constraints
3. **API Responses**: Removed `organization_id` fields from role and permission responses
4. **Business Logic**: Simplified to work with global roles and permissions only

All existing functionality continues to work, but now operates on a global scale rather than organization-specific scope.
