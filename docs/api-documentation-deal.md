## Deals API

### List Deals

*   **Description:** Retrieves deals with comprehensive filtering options.
*   **Method:** GET
*   **Endpoint:** `/api/deals`
*   **Query Parameters:**
    - `status`: Filter by deal status
    - `deal_type`: Filter by deal type
    - `closing_date_from`, `closing_date_to`: Date range filters

*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Deals retrieved successfully",
        "data": [
            {
                "id": 1,
                "uuid": "550e8400-e29b-41d4-a716-446655440003",
                "property_id": 1,
                "lead_id": 1,
                "organization_id": 1,
                "buyer_id": 2,
                "seller_id": 3,
                "deal_type": "assignment",
                "purchase_price": 180000.00,
                "sale_price": 195000.00,
                "assignment_fee": 15000.00,
                "contract_date": "2025-06-26",
                "closing_date": "2025-07-15",
                "inspection_period": 10,
                "earnest_money": 5000.00,
                "status": "active",
                "contract_terms": {
                    "financing_contingency": true,
                    "inspection_contingency": true,
                    "appraisal_contingency": false
                },
                "created_at": "2025-06-26T20:00:00.000000Z",
                "updated_at": "2025-06-26T20:00:00.000000Z"
            }
        ]
    }
    ```

### Create Deal

*   **Description:** Creates a new deal with comprehensive terms.
*   **Method:** POST
*   **Endpoint:** `/api/deals`
*   **Request:**

    ```json
    {
        "property_id": 1,
        "lead_id": 1,
        "organization_id": 1,
        "buyer_id": 2,
        "seller_id": 3,
        "deal_type": "assignment",
        "purchase_price": 180000.00,
        "sale_price": 195000.00,
        "assignment_fee": 15000.00,
        "contract_date": "2025-06-26",
        "closing_date": "2025-07-15",
        "inspection_period": 10,
        "earnest_money": 5000.00,
        "contract_terms": {
            "financing_contingency": true,
            "inspection_contingency": true,
            "appraisal_contingency": false
        }
    }
    ```
*   **Field Restrictions:**
    - `deal_type`: Allowed values are `assignment`, `double_close`, `wholesale`, `fix_flip`.
    - `status`: Allowed values are `active`, `pending`, `closed`, `cancelled`.
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Deal created successfully",
        "data": {
            "id": 2,
            "uuid": "550e8400-e29b-41d4-a716-446655440007",
            "property_id": 1,
            "lead_id": 1,
            "organization_id": 1,
            "buyer_id": 2,
            "seller_id": 3,
            "deal_type": "assignment",
            "purchase_price": 180000.00,
            "sale_price": 195000.00,
            "assignment_fee": 15000.00,
            "contract_date": "2025-06-26",
            "closing_date": "2025-07-15",
            "inspection_period": 10,
            "earnest_money": 5000.00,
            "status": "new",
            "contract_terms": {
                "financing_contingency": true,
                "inspection_contingency": true,
                "appraisal_contingency": false
            },
            "created_at": "2025-06-27T20:00:00.000000Z",
            "updated_at": "2025-06-27T20:00:00.000000Z"
        }
    }
    ```

### Get Deal

*   **Description:** Retrieves a specific deal by ID.
*   **Method:** GET
*   **Endpoint:** `/api/deals/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Deal retrieved successfully",
        "data": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440003",
            "property_id": 1,
            "lead_id": 1,
            "organization_id": 1,
            "buyer_id": 2,
            "seller_id": 3,
            "deal_type": "assignment",
            "purchase_price": 180000.00,
            "sale_price": 195000.00,
            "assignment_fee": 15000.00,
            "contract_date": "2025-06-26",
            "closing_date": "2025-07-15",
            "inspection_period": 10,
            "earnest_money": 5000.00,
            "status": "active",
            "contract_terms": {
                "financing_contingency": true,
                "inspection_contingency": true,
                "appraisal_contingency": false
            },
            "created_at": "2025-06-26T20:00:00.000000Z",
            "updated_at": "2025-06-26T20:00:00.000000Z"
        }
    }
    ```

### Update Deal

*   **Description:** Updates an existing deal.
*   **Method:** PUT
*   **Endpoint:** `/api/deals/{id}`
*   **Request:**

    ```json
    {
        "purchase_price": 185000.00,
        "sale_price": 200000.00,
        "closing_date": "2025-07-20",
        "status": "pending"
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Deal updated successfully",
        "data": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440003",
            "property_id": 1,
            "lead_id": 1,
            "organization_id": 1,
            "buyer_id": 2,
            "seller_id": 3,
            "deal_type": "assignment",
            "purchase_price": 185000.00,
            "sale_price": 200000.00,
            "assignment_fee": 15000.00,
            "contract_date": "2025-06-26",
            "closing_date": "2025-07-20",
            "inspection_period": 10,
            "earnest_money": 5000.00,
            "status": "pending",
            "contract_terms": {
                "financing_contingency": true,
                "inspection_contingency": true,
                "appraisal_contingency": false
            },
            "created_at": "2025-06-26T20:00:00.000000Z",
            "updated_at": "2025-06-27T20:00:00.000000Z"
        }
    }
    ```

### Delete Deal

*   **Description:** Deletes a specific deal.
*   **Method:** DELETE
*   **Endpoint:** `/api/deals/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Deal deleted successfully"
    }
    ```

## Deal Milestones API

### List Deal Milestones

*   **Description:** Retrieves milestones for a specific deal.
*   **Method:** GET
*   **Endpoint:** `/api/deals/{deal}/milestones`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Deal milestones retrieved successfully",
        "data": [
            {
                "id": 1,
                "uuid": "550e8400-e29b-41d4-a716-446655440008",
                "deal_id": 1,
                "milestone_type": "inspection",
                "title": "Property Inspection",
                "description": "Schedule and complete property inspection",
                "due_date": "2025-07-01",
                "completed_at": null,
                "is_critical": true,
                "created_at": "2025-06-27T20:00:00.000000Z",
                "updated_at": "2025-06-27T20:00:00.000000Z"
            }
        ]
    }
    ```

### Create Deal Milestone

*   **Description:** Creates a new milestone/task for a deal.
*   **Method:** POST
*   **Endpoint:** `/api/deal-milestones`
*   **Request:**

    ```json
    {
        "deal_id": 1,
        "milestone_type": "inspection",
        "title": "Property Inspection",
        "description": "Schedule and complete property inspection",
        "due_date": "2025-07-01",
        "is_critical": true
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Deal milestone created successfully",
        "data": {
            "id": 2,
            "uuid": "550e8400-e29b-41d4-a716-446655440009",
            "deal_id": 1,
            "milestone_type": "inspection",
            "title": "Property Inspection",
            "description": "Schedule and complete property inspection",
            "due_date": "2025-07-01",
            "completed_at": null,
            "is_critical": true,
            "created_at": "2025-06-27T20:00:00.000000Z",
            "updated_at": "2025-06-27T20:00:00.000000Z"
        }
    }
    ```

### Complete Deal Milestone

*   **Description:** Marks a milestone as completed.
*   **Method:** PATCH
*   **Endpoint:** `/api/deal-milestones/{milestone}/complete`
*   **Request:**

    ```json
    {
        "completed_at": "2025-06-28T10:00:00.000000Z"
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Deal milestone completed successfully",
        "data": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440008",
            "deal_id": 1,
            "milestone_type": "inspection",
            "title": "Property Inspection",
            "description": "Schedule and complete property inspection",
            "due_date": "2025-07-01",
            "completed_at": "2025-06-28T10:00:00.000000Z",
            "is_critical": true,
            "created_at": "2025-06-27T20:00:00.000000Z",
            "updated_at": "2025-06-28T10:00:00.000000Z"
        }
    }
    ```

### List All Deal Milestones

*   **Description:** Retrieves all deal milestones with filtering options.
*   **Method:** GET
*   **Endpoint:** `/api/deal-milestones`
*   **Query Parameters:**
    - `deal_id`: Filter by specific deal
    - `milestone_type`: Filter by milestone type
    - `is_critical`: Filter by critical milestones
    - `completed`: Filter by completion status
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "All deal milestones retrieved successfully",
        "data": [
            {
                "id": 1,
                "uuid": "550e8400-e29b-41d4-a716-446655440008",
                "deal_id": 1,
                "milestone_type": "inspection",
                "title": "Property Inspection",
                "description": "Schedule and complete property inspection",
                "due_date": "2025-07-01",
                "completed_at": null,
                "is_critical": true,
                "created_at": "2025-06-27T20:00:00.000000Z",
                "updated_at": "2025-06-27T20:00:00.000000Z"
            }
        ]
    }
    ```

### Get Deal Milestone

*   **Description:** Retrieves a specific deal milestone by ID.
*   **Method:** GET
*   **Endpoint:** `/api/deal-milestones/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Deal milestone retrieved successfully",
        "data": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440008",
            "deal_id": 1,
            "milestone_type": "inspection",
            "title": "Property Inspection",
            "description": "Schedule and complete property inspection",
            "due_date": "2025-07-01",
            "completed_at": null,
            "is_critical": true,
            "created_at": "2025-06-27T20:00:00.000000Z",
            "updated_at": "2025-06-27T20:00:00.000000Z"
        }
    }
    ```

### Update Deal Milestone

*   **Description:** Updates an existing deal milestone.
*   **Method:** PUT
*   **Endpoint:** `/api/deal-milestones/{id}`
*   **Request:**

    ```json
    {
        "title": "Updated Property Inspection",
        "description": "Updated description",
        "due_date": "2025-07-05",
        "is_critical": false
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Deal milestone updated successfully",
        "data": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440008",
            "deal_id": 1,
            "milestone_type": "inspection",
            "title": "Updated Property Inspection",
            "description": "Updated description",
            "due_date": "2025-07-05",
            "completed_at": null,
            "is_critical": false,
            "created_at": "2025-06-27T20:00:00.000000Z",
            "updated_at": "2025-06-28T11:00:00.000000Z"
        }
    }
    ```

### Delete Deal Milestone

*   **Description:** Deletes a specific deal milestone.
*   **Method:** DELETE
*   **Endpoint:** `/api/deal-milestones/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Deal milestone deleted successfully"
    }
