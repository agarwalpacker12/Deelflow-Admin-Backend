# API Documentation

## Introduction

This document provides detailed information about the API endpoints for the Real Estate Wholesaling Platform. The API follows RESTful conventions and returns JSON responses.

## Authentication

This API uses Sanctum authentication. You need to obtain an API token to access the protected endpoints.

### Base URL
```
https://api.dealflow.com/api
```

### Authentication Headers
```
Authorization: Bearer {your-api-token}
Content-Type: application/json
Accept: application/json
```

## Error Responses

All endpoints may return the following error responses:

### 400 Bad Request
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "price": ["The price must be a number."]
    }
}
```

### 401 Unauthorized
```json
{
    "status": "error",
    "message": "Unauthenticated",
    "error": "Token not provided or invalid"
}
```

### 403 Forbidden
```json
{
    "status": "error",
    "message": "Forbidden",
    "error": "Insufficient permissions"
}
```

### 404 Not Found
```json
{
    "status": "error",
    "message": "Resource not found",
    "error": "The requested resource could not be found"
}
```

### 422 Unprocessable Entity
```json
{
    "status": "error",
    "message": "Validation error",
    "errors": {
        "field_name": ["Specific validation error message"]
    }
}
```

### 500 Internal Server Error
```json
{
    "status": "error",
    "message": "Internal server error",
    "error": "An unexpected error occurred"
}
```

## Rate Limiting

The API implements rate limiting to ensure fair usage:

- **Authenticated requests**: 1000 requests per hour
- **Unauthenticated requests**: 100 requests per hour

Rate limit headers are included in all responses:
```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1640995200
```

## Pagination

List endpoints support pagination with the following parameters:

- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 10, max: 100)

Pagination metadata is included in the response:
```json
{
    "meta": {
        "current_page": 1,
        "per_page": 10,
        "total": 100,
        "last_page": 10,
        "from": 1,
        "to": 10
    },
    "links": {
        "first": "/api/endpoint?page=1",
        "last": "/api/endpoint?page=10",
        "prev": null,
        "next": "/api/endpoint?page=2"
    }
}
```

## Filtering and Searching

Many list endpoints support filtering and searching:

### Common Filter Parameters
- `search`: General text search across relevant fields
- `status`: Filter by status
- `created_from`, `created_to`: Date range filters
- `sort_by`: Field to sort by
- `sort_direction`: `asc` or `desc`

### Example
```
GET /api/properties?search=austin&status=active&price_min=100000&price_max=300000&sort_by=created_at&sort_direction=desc
