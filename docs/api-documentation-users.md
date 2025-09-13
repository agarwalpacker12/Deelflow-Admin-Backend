# User Management API Documentation

This document outlines all the user management APIs available in the application, providing dual functionality for both Super Admin and Organization Admin users.

## Overview

The user management system provides consolidated endpoints that intelligently serve different data and functionality based on the authenticated user's privileges:

1. **Super Admin Level**: Cross-organization user management with full visibility
2. **Organization Admin Level**: Organization-scoped user management with restricted access

## Authentication & Authorization

All endpoints require authentication via Sanctum token in the Authorization header:
```
Authorization: Bearer {token}
```

### Access Levels
- **Super Admin**: Can manage users across all organizations, includes organization details in responses
- **Organization Admin**: Can only manage users within their own organization, receives 403 errors for cross-organization access

## User Management Endpoints (Consolidated)

### 1. Get Users

**GET** `/api/users`

Retrieves users based on user type with dual functionality:
- **Super Admin**: All users across all organizations with pagination and filtering
- **Organization Admin**: Users within their organization only with pagination and filtering

**Query Parameters:**
- `page` (optional): Page number for pagination (default: 1)
- `per_page` (optional): Items per page (default: 10, max: 100)
- `organization_id` (optional): Filter by organization ID (Super Admin only)
- `role` (optional): Filter by role name
- `status` (optional): Filter by user status
- `search` (optional): Search in first_name, last_name, or email

**Response:**

**For Super Admin:**
```json
{
  "status": "success",
  "message": "Users retrieved successfully",
  "data": {
    "users": [
      {
        "id": 1,
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "email": "user@example.com",
        "first_name": "John",
        "last_name": "Doe",
        "full_name": "John Doe",
        "phone": "+1234567890",
        "status": "active",
        "is_active": true,
        "is_verified": true,
        "organization": {
          "id": 1,
          "name": "Example Organization",
          "slug": "example-organization",
          "subscription_status": "active"
        },
        "roles": [
          {
            "id": 1,
            "name": "admin",
            "label": "Administrator",
            "permissions": [
              {
                "id": 1,
                "name": "manage_roles",
                "label": "Manage Roles"
              }
            ]
          }
        ],
        "created_at": "2025-08-11T09:00:00.000000Z",
        "updated_at": "2025-08-11T09:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 25,
      "last_page": 3,
      "from": 1,
      "to": 10
    },
    "filters_applied": {
      "organization_id": 1,
      "role": "admin"
    }
  }
}
```

**For Organization Admin:**
```json
{
  "status": "success",
  "message": "Organization users retrieved successfully",
  "data": {
    "users": [
      {
        "id": 1,
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "email": "user@example.com",
        "first_name": "John",
        "last_name": "Doe",
        "full_name": "John Doe",
        "phone": "+1234567890",
        "status": "active",
        "is_active": true,
        "is_verified": true,
        "roles": [
          {
            "id": 2,
            "name": "staff",
            "label": "Staff",
            "permissions": [
              {
                "id": 4,
                "name": "manage_lead",
                "label": "Manage Leads"
              }
            ]
          }
        ],
        "created_at": "2025-08-11T09:00:00.000000Z",
        "updated_at": "2025-08-11T09:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 8,
      "last_page": 1,
      "from": 1,
      "to": 8
    },
    "filters_applied": {
      "role": "staff"
    }
  }
}
```

### 2. Get User Details

**GET** `/api/users/{user}`

Retrieves detailed information about a specific user based on user type:
- **Super Admin**: Any user across organizations (includes organization details)
- **Organization Admin**: Users within their organization only

**Response:**

**For Super Admin:**
```json
{
  "status": "success",
  "message": "User details retrieved successfully",
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "email": "user@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "full_name": "John Doe",
    "phone": "+1234567890",
    "status": "active",
    "is_active": true,
    "is_verified": true,
    "level": 1,
    "points": 100,
    "organization": {
      "id": 1,
      "name": "Example Organization",
      "slug": "example-organization",
      "subscription_status": "active"
    },
    "roles": [
      {
        "id": 1,
        "name": "admin",
        "label": "Administrator",
        "permissions": [
          {
            "id": 1,
            "name": "manage_roles",
            "label": "Manage Roles"
          }
        ]
      }
    ],
    "created_at": "2025-08-11T09:00:00.000000Z",
    "updated_at": "2025-08-11T09:00:00.000000Z",
    "last_login_at": "2025-08-11T08:30:00.000000Z"
  }
}
```

**For Organization Admin:**
```json
{
  "status": "success",
  "message": "User details retrieved successfully",
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "email": "user@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "full_name": "John Doe",
    "phone": "+1234567890",
    "status": "active",
    "is_active": true,
    "is_verified": true,
    "level": 1,
    "points": 50,
    "roles": [
      {
        "id": 2,
        "name": "staff",
        "label": "Staff",
        "permissions": [
          {
            "id": 4,
            "name": "manage_lead",
            "label": "Manage Leads"
          }
        ]
      }
    ],
    "created_at": "2025-08-11T09:00:00.000000Z",
    "updated_at": "2025-08-11T09:00:00.000000Z",
    "last_login_at": "2025-08-11T08:30:00.000000Z"
  }
}
```

**Error Response (Organization Admin accessing cross-organization user):**
```json
{
  "status": "error",
  "message": "Access denied. You need same organization privileges to access this user.",
  "error_code": "FORBIDDEN"
}
```

### 3. Update User Roles

**PUT** `/api/users/{user}/roles`

Updates a user's roles based on user type:
- **Super Admin**: Can update roles for any user (cross-organization)
- **Organization Admin**: Can update roles for users within their organization only

**Request Body:**
```json
{
  "roles": ["admin", "staff"]
}
```

**Response:**

**For Super Admin:**
```json
{
  "status": "success",
  "message": "User roles updated successfully",
  "data": {
    "user_id": 1,
    "user_email": "user@example.com",
    "organization": {
      "id": 1,
      "name": "Example Organization"
    },
    "roles": [
      {
        "id": 1,
        "name": "admin",
        "label": "Administrator",
        "permissions": [
          {
            "id": 1,
            "name": "manage_roles",
            "label": "Manage Roles"
          }
        ]
      }
    ],
    "updated_at": "2025-08-11T09:00:00.000000Z"
  }
}
```

**For Organization Admin:**
```json
{
  "status": "success",
  "message": "User roles updated successfully",
  "data": {
    "user_id": 1,
    "user_email": "user@example.com",
    "roles": [
      {
        "id": 2,
        "name": "staff",
        "label": "Staff",
        "permissions": [
          {
            "id": 4,
            "name": "manage_lead",
            "label": "Manage Leads"
          }
        ]
      }
    ],
    "updated_at": "2025-08-11T09:00:00.000000Z"
  }
}
```

**Error Response (Organization Admin updating cross-organization user):**
```json
{
  "status": "error",
  "message": "Access denied. You need same organization privileges to update this user.",
  "error_code": "FORBIDDEN"
}
```

### 4. Update User Status

**PATCH** `/api/users/{user}/status`

Updates a user's status based on user type:
- **Super Admin**: Can update status for any user
- **Organization Admin**: Can update status for users within their organization only

**Request Body:**
```json
{
  "status": "active"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "User status updated successfully",
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "email": "user@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "full_name": "John Doe",
    "phone": "+1234567890",
    "status": "active",
    "is_active": true,
    "is_verified": true,
    "level": 1,
    "points": 50,
    "roles": [
      {
        "id": 2,
        "name": "staff",
        "label": "Staff",
        "permissions": [
          {
            "id": 4,
            "name": "manage_lead",
            "label": "Manage Leads"
          }
        ]
      }
    ],
    "created_at": "2025-08-11T09:00:00.000000Z",
    "updated_at": "2025-08-12T09:00:00.000000Z",
    "last_login_at": "2025-08-11T08:30:00.000000Z"
  }
}
```

## User Status Values

Users can have the following status values:
- `active`: User is active and can access the system
- `inactive`: User is temporarily disabled
- `pending`: User account is pending activation
- `suspended`: User account is suspended

## User Role Integration

Users are assigned roles through the many-to-many relationship with the roles table. Each role contains multiple permissions that define what actions the user can perform within the system.

**Important**: The system now uses **global roles and permissions** that are shared across all organizations, not organization-specific.

### Role Assignment Rules
1. **Super Admin**: Can assign any global role to any user across all organizations
2. **Organization Admin**: Can assign global roles to users within their organization
3. **Role Validation**: All role assignments are validated to ensure roles exist in the global role system
4. **Permission Inheritance**: Users inherit all permissions from their assigned global roles
5. **Global Consistency**: Same roles and permissions are available across all organizations

## Error Responses

### Validation Errors
```json
{
  "status": "error",
  "message": "Validation failed for updating user roles",
  "error_code": "VALIDATION_ERROR",
  "errors": [
    "The roles field is required.",
    "The roles.0 field must be a string."
  ]
}
```

### Authorization Errors
```json
{
  "status": "error",
  "message": "Access denied. You need same organization privileges to access this user.",
  "error_code": "FORBIDDEN"
}
```

### Business Logic Errors
```json
{
  "status": "error",
  "message": "Some roles do not exist",
  "error_code": "INVALID_ROLES",
  "details": {
    "requested_roles": ["invalid_role"]
  },
  "suggestions": [
    "Ensure all roles exist in the global role system",
    "Check role names for typos"
  ]
}
```

### Not Found Errors
```json
{
  "status": "error",
  "message": "User not found",
  "error_code": "NOT_FOUND"
}
```

## Security Features

1. **Organization Isolation**: Organization admins can only access users within their organization
2. **Super Admin Protection**: Super admin access is restricted to configured email addresses
3. **Global Role Validation**: All role assignments are validated against the global role system
4. **Cross-organization Prevention**: Built-in checks prevent unauthorized cross-organization access
5. **Audit Trail**: All user role changes are logged with timestamps and user information
6. **Global Consistency**: Same security model applies across all organizations using global roles

## Rate Limiting

All endpoints are subject to rate limiting:
- Authenticated users: 1000 requests per hour
- Unauthenticated users: 100 requests per hour

Rate limit headers are included in responses:
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Remaining requests
- `X-RateLimit-Reset`: Reset timestamp

## Best Practices

1. **Pagination**: Always use pagination for user lists to improve performance
2. **Filtering**: Use appropriate filters to reduce response size and improve query performance
3. **Role Management**: Regularly review and audit user role assignments
4. **Status Monitoring**: Monitor user status changes for security and compliance
5. **Error Handling**: Implement proper error handling for all API responses

## Related Documentation

- [Role and Permission Management API](./api-documentation-roles-permissions.md)
- [Authentication API](./api-documentation-auth.md)
- [Organization Management API](./api-documentation-organizations.md)
