# Organizations API

The Organizations API allows you to manage organizations within the system.

## Organization Model

The `Organization` model contains the following fields:

- `id` (integer, read-only): The unique identifier for the organization.
- `name` (string, required): The name of the organization.
- `slug` (string, unique, nullable): The slug for the organization.
- `subscription_status` (string, nullable): The subscription status of the organization. Can be `new`, `active`, `suspended`, or `waiting`.
- `industry` (string, nullable): The industry of the organization.
- `organization_size` (string, nullable): The size of the organization.
- `business_email` (string, nullable): The business email of the organization.
- `business_phone` (string, nullable): The business phone of the organization.
- `website` (string, nullable): The website of the organization.
- `support_email` (string, nullable): The support email of the organization.
- `street_address` (string, nullable): The street address of the organization.
- `city` (string, nullable): The city of the organization.
- `state_province` (string, nullable): The state or province of the organization.
- `zip_postal_code` (string, nullable): The zip or postal code of the organization.
- `country` (string, nullable): The country of the organization.
- `timezone` (string, nullable): The timezone of the organization.
- `language` (string, nullable): The language of the organization.
- `created_at` (datetime, read-only): The date and time the organization was created.
- `updated_at` (datetime, read-only): The date and time the organization was last updated.

## API Endpoints

### GET /api/organizations

- **Description:** Retrieve organizations based on user role:
  - **Super Admin**: Returns all organizations with pagination and filtering
  - **Regular Users**: Returns only their own organization
- **Query Parameters (Super Admin only):**
  - `page` (integer, optional): Page number for pagination (default: 1)
  - `per_page` (integer, optional): Items per page (default: 10, max: 100)
  - `subscription_status` (string, optional): Filter by subscription status (`new`, `active`, `suspended`, `waiting`)
  - `search` (string, optional): Search organizations by name
- **Response:**

**For Super Admin:**
```json
{
  "status": "success",
  "message": "Organizations retrieved successfully",
  "data": {
    "organizations": [
      {
        "id": 1,
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "name": "Example Organization",
        "slug": "example-organization",
        "subscription_status": "active",
        "industry": "Technology",
        "organization_size": "50-100",
        "business_email": "contact@example.com",
        "business_phone": "+1234567890",
        "website": "https://example.com",
        "users_count": 25,
        "roles_count": 2,
        "permissions_count": 6,
        "created_at": "2025-08-11T09:00:00.000000Z",
        "updated_at": "2025-08-11T09:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 5,
      "last_page": 1,
      "from": 1,
      "to": 5
    },
    "filters_applied": {
      "subscription_status": "active"
    }
  }
}
```

**For Regular Users:**
```json
{
  "status": "success",
  "message": "Organization retrieved successfully",
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "name": "User's Organization",
    "slug": "users-organization",
    "subscription_status": "active",
    "industry": "Technology",
    "organization_size": "10-50",
    "business_email": "contact@userorg.com",
    "business_phone": "+1234567890",
    "website": "https://userorg.com",
    "support_email": "support@userorg.com",
    "street_address": "123 Main St",
    "city": "New York",
    "state_province": "NY",
    "zip_postal_code": "10001",
    "country": "USA",
    "timezone": "America/New_York",
    "language": "en",
    "created_at": "2025-08-11T09:00:00.000000Z",
    "updated_at": "2025-08-11T09:00:00.000000Z"
  }
}
```

### GET /api/organizations/{id}

- **Description:** Retrieve a specific organization by its ID.
- **Parameters:**
  - `id` (integer, required): The ID of the organization to retrieve.
- **Response:** A JSON object representing the organization.

### POST /api/organizations

- **Description:** Create a new organization.
- **Request Body:** A JSON object containing the organization's data.
  - `name` (string, required)
  - `industry` (string, nullable)
  - `organization_size` (string, nullable)
  - `business_email` (string, nullable)
  - `business_phone` (string, nullable)
  - `website` (string, nullable)
  - `support_email` (string, nullable)
  - `street_address` (string, nullable)
  - `city` (string, nullable)
  - `state_province` (string, nullable)
  - `zip_postal_code` (string, nullable)
  - `country` (string, nullable)
  - `timezone` (string, nullable)
  - `language` (string, nullable)
- **Response:** A JSON object representing the newly created organization.

### PUT /api/organizations/{id}

- **Description:** Update an existing organization.
- **Parameters:**
  - `id` (integer, required): The ID of the organization to update.
- **Request Body:** A JSON object containing the fields to update.
- **Response:** A JSON object representing the updated organization.

### DELETE /api/organizations/{id}

- **Description:** Delete an organization.
- **Parameters:**
  - `id` (integer, required): The ID of the organization to delete.
- **Response:** A success message.

### GET /api/organizations/status

- **Description:** Get the subscription status of the current user's organization.
- **Response:** A JSON object with the organization's subscription status.

### PUT /api/organizations/{id}/subscription-status

- **Description:** Update the subscription status of an organization.
- **Parameters:**
  - `id` (integer, required): The ID of the organization to update.
- **Request Body:**
  - `subscription_status` (string, required): The new subscription status. Can be `new`, `active`, `suspended`, or `waiting`.
- **Response:** A JSON object representing the updated organization.

### DELETE /api/organizations/{id}/users/{user_id}

- **Description:** Remove a user from an organization.
- **Parameters:**
  - `id` (integer, required): The ID of the organization.
  - `user_id` (integer, required): The ID of the user to remove.
- **Response:** A success message.

### PUT /api/organizations/{id}/users/{user_id}/status

- **Description:** Update a user's status within an organization.
- **Parameters:**
  - `id` (integer, required): The ID of the organization.
  - `user_id` (integer, required): The ID of the user to update.
- **Request Body:**
  - `status` (string, required): The new status for the user. Can be `active` or `inactive`.
- **Response:** A JSON object representing the updated user.
