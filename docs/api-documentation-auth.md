## Invitaion API

### Register User

*   **Description:** Registers a new user and organization.
*   **Method:** POST
*   **Endpoint:** `/api/register`
*   **Request:**

    ```json
    {
        "email": "user@example.com",
        "password": "password",
        "password_confirmation": "password",
        "first_name": "John",
        "last_name": "Doe",
        "organization_name": "Real Estate Ventures LLC",
        "phone": "+14155552671"
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "User registered successfully",
        "data": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440000",
            "email": "user@example.com",
            "first_name": "John",
            "last_name": "Doe",
            "organization": {
                "id": 1,
                "name": "Real Estate Ventures LLC",
                "slug": "real-estate-ventures-llc",
                "subscription_status": "active",
                "created_at": "2025-06-26T20:00:00.000000Z",
                "updated_at": "2025-06-26T20:00:00.000000Z"
            },
            "phone": "+14155552671",
            "level": 1,
            "points": 0,
            "is_verified": false,
            "is_active": true,
            "status": "active",
            "created_at": "2025-06-26T20:00:00.000000Z",
            "updated_at": "2025-06-26T20:00:00.000000Z"
        }
    }
    ```

### Register Invitee User

*   **Description:** Registers a new user using an invitation token to accept an invitation and join an organization.
*   **Method:** POST
*   **Endpoint:** `/api/invitee-register`
*   **Request:**

    ```json
    {
        "password": "password",
        "password_confirmation": "password",
        "first_name": "John",
        "last_name": "Doe",
        "phone": "+14155552671",
        "invitation_token": "2yWyOxiZHgqVcNVQTXYdzte7boypWh5tnaadaHSQ"
    }
    ```

*   **Response (Success - 201 Created):**

    ```json
    {
        "status": "success",
        "message": "User registered successfully",
        "data": {
            "id": 74,
            "uuid": "db7084a4-ded1-44c7-a690-6f7c7128997f",
            "email": "Prosuntest@mailinator.com",
            "first_name": "Prosun",
            "last_name": "invitee",
            "organization": {
                "id": 1,
                "name": "Real Estate Ventures LLC",
                "slug": "real-estate-ventures-llc",
                "subscription_status": "active"
            },
            "phone": "9868985856",
            "level": 1,
            "points": 0,
            "is_verified": false,
            "is_active": true,
            "created_at": "2025-08-08T08:44:46.000000Z",
            "updated_at": "2025-08-08T08:44:46.000000Z"
        }
    }
    ```

*   **Response (Error - 422 Unprocessable Entity - Validation Error):**

    ```json
    {
        "status": "error",
        "message": "Validation failed for user registration. Please check the provided data and correct the errors.",
        "error": {
            "code": "VALIDATION_ERROR",
            "details": {
                "failed_fields": ["password", "invitation_token"],
                "field_errors": {
                    "password": ["The password field is required.", "The password must be at least 8 characters."],
                    "password_confirmation": ["The password confirmation does not match."],
                    "first_name": ["The first name field is required."],
                    "last_name": ["The last name field is required."],
                    "invitation_token": ["The selected invitation token is invalid."]
                },
                "suggestions": [
                    "Password must be at least 8 characters long and include a mix of letters and numbers",
                    "Ensure password and password confirmation match exactly",
                    "Verify the invitation token is valid and has not expired"
                ],
                "total_errors": 3
            },
            "timestamp": "2025-08-09T22:10:00.000000Z"
        }
    }
    ```

*   **Response (Error - 409 Conflict - Database Error):**

    ```json
    {
        "status": "error",
        "message": "Database error occurred during user registration.",
        "error": {
            "code": "DATABASE_ERROR",
            "details": {
                "constraint_type": "unique_violation",
                "duplicate_field": "email",
                "suggestions": [
                    "The email address is already registered in the system",
                    "Use a different email address or contact support if this is unexpected"
                ],
                "operation": "user registration",
                "resource_type": "user"
            },
            "timestamp": "2025-08-09T22:10:00.000000Z"
        }
    }
    ```

*   **Response (Error - 500 Internal Server Error):**

    ```json
    {
        "status": "error",
        "message": "An internal server error occurred during user registration. Our team has been notified.",
        "error": {
            "code": "INTERNAL_SERVER_ERROR",
            "details": {
                "operation": "user registration",
                "suggestions": [
                    "Please try again in a few moments",
                    "If the problem persists, contact support",
                    "Check if all required services are running"
                ]
            },
            "timestamp": "2025-08-09T22:10:00.000000Z"
        }
    }
    ```


### Login User

*   **Description:** Authenticates a user and returns an API token.
*   **Method:** POST
*   **Endpoint:** `/api/login`
*   **Request:**

    ```json
    {
        "email": "user@example.com",
        "password": "password"
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "User logged in successfully",
        "data": {
            "token": "1|abcdef123456789",
            "user": {
                "id": 1,
                "uuid": "550e8400-e29b-41d4-a716-446655440000",
                "email": "user@example.com",
                "first_name": "John",
                "last_name": "Doe"
            }
        }
    }
    ```

### Get Current User

*   **Description:** Retrieves the authenticated user's profile information.
*   **Method:** GET
*   **Endpoint:** `/api/user`
*   **Headers:** `Authorization: Bearer {token}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "User retrieved successfully",
        "data": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440000",
            "email": "user@example.com",
            "first_name": "John",
            "last_name": "Doe",
            "company_name": null,
            "phone": "+14155552671",
            "level": 1,
            "points": 0,
            "is_verified": false,
            "is_active": true,
            "status": "active",
            "created_at": "2025-06-26T20:00:00.000000Z",
            "updated_at": "2025-06-26T20:00:00.000000Z"
        }
    }
    ```

### Logout User

*   **Description:** Logs out the authenticated user and invalidates the token.
*   **Method:** POST
*   **Endpoint:** `/api/logout`
*   **Headers:** `Authorization: Bearer {token}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "User logged out successfully"
    }


### Invite User

*   **Description:** Invites users to join an organization.
*   **Method:** POST
*   **Endpoint:** `/api/invitations`
*   **Headers:** `Authorization: Bearer {token}`
*   **Request:**

    ```json
    {
        "email": "user@example.com",
        "role": "staff"
    }
    ```

*   **Response (Success - 201 Created):**

    ```json
    {
        "status": "success",
        "message": "Invitation sent successfully",
        "data": {
            "invitation_id": 123,
            "email": "user@example.com",
            "role": "staff",
            "created_at": "2025-08-09T21:30:00.000000Z"
        }
    }
    ```

*   **Response (Error - 400 Bad Request - Invitation Already Exists):**

    ```json
    {
        "status": "error",
        "message": "An invitation has already been sent to this email address.",
        "error": {
            "code": "INVITATION_ALREADY_EXISTS",
            "details": {
                "email": "user@example.com",
                "existing_invitation_id": 456,
                "existing_invitation_created": "2025-08-08T15:30:00.000000Z",
                "suggestions": [
                    "Check if the user has already received an invitation",
                    "Consider resending the existing invitation if needed",
                    "Use a different email address if this is a different user"
                ]
            },
            "timestamp": "2025-08-09T21:30:00.000000Z"
        }
    }
    ```

*   **Response (Error - 422 Unprocessable Entity - Validation Error):**

    ```json
    {
        "status": "error",
        "message": "Validation failed for invitation creation. Please check the provided data and correct the errors.",
        "error": {
            "code": "VALIDATION_ERROR",
            "details": {
                "failed_fields": ["email", "role"],
                "field_errors": {
                    "email": ["The email field is required."],
                    "role": ["The selected role is invalid."]
                },
                "suggestions": [
                    "Ensure the email address is in a valid format (e.g., user@example.com)"
                ],
                "total_errors": 2
            },
            "timestamp": "2025-08-09T21:30:00.000000Z"
        }
    }
    ```

### Validate Invitation Token

*   **Description:** Validates an invitation token to verify its authenticity and retrieve invitation details.
*   **Method:** GET
*   **Endpoint:** `/api/validate-invitation?token={invitationtoken}`
*   **Parameters:**
    *   `token` (string, required): The invitation token to validate
*   **Response (Success - 200 OK):**

    ```json
    {
        "status": "success",
        "message": "Invitation validated successfully",
        "data": {
            "email": "example@mailinator.com",
            "role": "staff",
            "organization": {
                "id": 1,
                "name": "Unitech"
            },
            "token": "2yWyOxiZHgqVcNVQTXYdzte7boypWh5tnaadaHSQ"
        }
    }
    ```

*   **Response (Error - 400 Bad Request - Missing Token):**

    ```json
    {
        "status": "error",
        "message": "Invitation token is required.",
        "error": {
            "code": "MISSING_TOKEN",
            "details": {
                "suggestions": [
                    "Ensure the invitation link includes a valid token parameter",
                    "Check if the invitation link was copied correctly"
                ]
            },
            "timestamp": "2025-08-09T21:30:00.000000Z"
        }
    }
    ```

*   **Response (Error - 400 Bad Request - Invalid Token):**

    ```json
    {
        "status": "error",
        "message": "Invalid or expired invitation token.",
        "error": {
            "code": "INVALID_INVITATION_TOKEN",
            "details": {
                "token": "invalid_token_here",
                "suggestions": [
                    "Request a new invitation from your organization administrator",
                    "Ensure you are using the most recent invitation link",
                    "Check if the invitation link was copied correctly"
                ]
            },
            "timestamp": "2025-08-09T21:30:00.000000Z"
        }
    }
    ```
