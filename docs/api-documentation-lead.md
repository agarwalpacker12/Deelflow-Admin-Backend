## Leads API

### List Leads

*   **Description:** Retrieves a paginated list of leads with filtering options.
*   **Method:** GET
*   **Endpoint:** `/api/leads`
*   **Query Parameters:**
    - `page` (integer): Page number for pagination
    - `per_page` (integer): Number of items per page (max 100)
    - `status` (string): Filter by lead status
    - `ai_score_min` (integer): Minimum AI score filter
    - `search` (string): Search in names, email, or address

*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Leads retrieved successfully",
        "data": [
            {
                "id": 1,
                "uuid": "550e8400-e29b-41d4-a716-446655440001",
                "user_id": 1,
                "first_name": "Jane",
                "last_name": "Smith",
                "email": "jane.smith@example.com",
                "phone": "+14155552671",
                "property_address": "123 Main St",
                "property_city": "Austin",
                "property_state": "TX",
                "property_zip": "78701",
                "property_type": "single_family",
                "ai_score": 85,
                "motivation_score": 90,
                "urgency_score": 75,
                "financial_score": 80,
                "source": "website_form",
                "estimated_value": 250000.00,
                "mortgage_balance": 180000.00,
                "asking_price": 220000.00,
                "status": "qualified",
                "preferred_contact_method": "phone",
                "next_action": "Schedule property visit",
                "next_action_date": "2025-06-28",
                "created_at": "2025-06-26T20:00:00.000000Z",
                "updated_at": "2025-06-26T20:00:00.000000Z"
            }
        ],
        "meta": {
            "current_page": 1,
            "per_page": 10,
            "total": 1,
            "last_page": 1
        }
    }
    ```

### Create Lead

*   **Description:** Creates a new lead with comprehensive information.
*   **Method:** POST
*   **Endpoint:** `/api/leads`
*   **Request:**

    ```json
    {
        "first_name": "Jane",
        "last_name": "Smith",
        "email": "jane.smith@example.com",
        "phone": "+14155552671",
        "property_address": "123 Main St",
        "property_city": "Austin",
        "property_state": "TX",
        "property_zip": "78701",
        "property_type": "single_family",
        "source": "website_form",
        "estimated_value": 250000.00,
        "mortgage_balance": 180000.00,
        "asking_price": 220000.00,
        "preferred_contact_method": "phone"
    }
    ```
*   **Field Restrictions:**
    - `property_type`: Allowed values are `single_family`, `townhouse`, `condo`, `duplex`, `multi_family`, `mobile_home`.
    - `preferred_contact_method`: Allowed values are `phone`, `email`, `text`.
    - `status`: Allowed values are `new`, `contacted`, `qualified`, `negotiating`, `contract`, `closed`, `dead`.
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Lead created successfully",
        "data": {
            "id": 2,
            "uuid": "550e8400-e29b-41d4-a716-446655440005",
            "user_id": 1,
            "first_name": "Jane",
            "last_name": "Smith",
            "email": "jane.smith@example.com",
            "phone": "+14155552671",
            "property_address": "123 Main St",
            "property_city": "Austin",
            "property_state": "TX",
            "property_zip": "78701",
            "property_type": "single_family",
            "ai_score": 0,
            "motivation_score": 0,
            "urgency_score": 0,
            "financial_score": 0,
            "source": "website_form",
            "estimated_value": 250000.00,
            "mortgage_balance": 180000.00,
            "asking_price": 220000.00,
            "status": "new",
            "preferred_contact_method": "phone",
            "next_action": null,
            "next_action_date": null,
            "created_at": "2025-06-27T20:00:00.000000Z",
            "updated_at": "2025-06-27T20:00:00.000000Z"
        }
    }
    ```

### Get Lead

*   **Description:** Retrieves a specific lead by ID.
*   **Method:** GET
*   **Endpoint:** `/api/leads/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Lead retrieved successfully",
        "data": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440001",
            "user_id": 1,
            "first_name": "Jane",
            "last_name": "Smith",
            "email": "jane.smith@example.com",
            "phone": "+14155552671",
            "property_address": "123 Main St",
            "property_city": "Austin",
            "property_state": "TX",
            "property_zip": "78701",
            "property_type": "single_family",
            "ai_score": 85,
            "motivation_score": 90,
            "urgency_score": 75,
            "financial_score": 80,
            "source": "website_form",
            "estimated_value": 250000.00,
            "mortgage_balance": 180000.00,
            "asking_price": 220000.00,
            "status": "qualified",
            "preferred_contact_method": "phone",
            "next_action": "Schedule property visit",
            "next_action_date": "2025-06-28",
            "created_at": "2025-06-26T20:00:00.000000Z",
            "updated_at": "2025-06-26T20:00:00.000000Z"
        }
    }
    ```

### Update Lead

*   **Description:** Updates an existing lead.
*   **Method:** PUT
*   **Endpoint:** `/api/leads/{id}`
*   **Request:**

    ```json
    {
        "first_name": "Jane",
        "last_name": "Smith",
        "email": "jane.smith@example.com",
        "phone": "+14155552671",
        "status": "qualified",
        "next_action": "Schedule property visit",
        "next_action_date": "2025-06-28"
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Lead updated successfully",
        "data": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440001",
            "user_id": 1,
            "first_name": "Jane",
            "last_name": "Smith",
            "email": "jane.smith@example.com",
            "phone": "+14155552671",
            "property_address": "123 Main St",
            "property_city": "Austin",
            "property_state": "TX",
            "property_zip": "78701",
            "property_type": "single_family",
            "ai_score": 85,
            "motivation_score": 90,
            "urgency_score": 75,
            "financial_score": 80,
            "source": "website_form",
            "estimated_value": 250000.00,
            "mortgage_balance": 180000.00,
            "asking_price": 220000.00,
            "status": "qualified",
            "preferred_contact_method": "phone",
            "next_action": "Schedule property visit",
            "next_action_date": "2025-06-28",
            "created_at": "2025-06-26T20:00:00.000000Z",
            "updated_at": "2025-06-27T20:00:00.000000Z"
        }
    }
    ```

### Delete Lead

*   **Description:** Deletes a specific lead.
*   **Method:** DELETE
*   **Endpoint:** `/api/leads/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Lead deleted successfully"
    }
    ```

### Get Lead AI Score

*   **Description:** Retrieves AI-powered scoring for a specific lead.
*   **Method:** GET
*   **Endpoint:** `/api/leads/{lead}/ai-score`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Lead AI score retrieved successfully",
        "data": {
            "lead_id": 1,
            "ai_score": 85,
            "motivation_score": 90,
            "urgency_score": 75,
            "financial_score": 80,
            "analysis": {
                "motivation_factors": ["Divorce situation", "Financial distress"],
                "urgency_indicators": ["Needs to sell within 30 days"],
                "financial_capability": "Strong equity position"
            }
        }
    }
