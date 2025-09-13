## Properties API

### List Properties

*   **Description:** Retrieves a paginated list of properties with advanced filtering.
*   **Method:** GET
*   **Endpoint:** `/api/properties`
*   **Query Parameters:**
    - `page`, `per_page`: Pagination
    - `status`: Filter by property status
    - `city`, `state`, `zip`: Location filters
    - `price_min`, `price_max`: Price range filters
    - `bedrooms`, `bathrooms`: Property specification filters
    - `transaction_type`: Filter by transaction type
    - `ai_score_min`: Minimum AI score filter

*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Properties retrieved successfully",
        "data": [
            {
                "id": 1,
                "uuid": "550e8400-e29b-41d4-a716-446655440002",
                "user_id": 1,
                "address": "456 Oak Avenue",
                "unit": "Unit A",
                "city": "Austin",
                "state": "TX",
                "zip": "78702",
                "county": "Travis",
                "property_type": "single_family",
                "bedrooms": 3,
                "bathrooms": 2.5,
                "square_feet": 1800,
                "lot_size": 0.25,
                "year_built": 1995,
                "purchase_price": 180000.00,
                "arv": 250000.00,
                "repair_estimate": 25000.00,
                "holding_costs": 5000.00,
                "profit_potential": 40000.00,
                "ai_score": 92,
                "transaction_type": "assignment",
                "assignment_fee": 15000.00,
                "status": "active",
                "view_count": 45,
                "save_count": 8,
                "inquiry_count": 3,
                "images": [
                    "https://example.com/images/property1_1.jpg",
                    "https://example.com/images/property1_2.jpg"
                ],
                "created_at": "2025-06-26T20:00:00.000000Z",
                "updated_at": "2025-06-26T20:00:00.000000Z"
            }
        ]
    }
    ```

### Create Property

*   **Description:** Creates a new property listing with comprehensive details.
*   **Method:** POST
*   **Endpoint:** `/api/properties`
*   **Request:**

    ```json
    {
        "address": "456 Oak Avenue",
        "unit": "Unit A",
        "city": "Austin",
        "state": "TX",
        "zip": "78702",
        "county": "Travis",
        "property_type": "single_family",
        "bedrooms": 3,
        "bathrooms": 2.5,
        "square_feet": 1800,
        "lot_size": 0.25,
        "year_built": 1995,
        "purchase_price": 180000.00,
        "arv": 250000.00,
        "repair_estimate": 25000.00,
        "holding_costs": 5000.00,
        "transaction_type": "assignment",
        "assignment_fee": 15000.00,
        "description": "Beautiful single-family home with great potential",
        "seller_notes": "Motivated seller, quick closing preferred"
    }
    ```
*   **Required Fields:**
    - `address` (string, max 500 chars)
    - `city` (string, max 100 chars)
    - `state` (string, max 2 chars)
    - `zip` (string, max 10 chars)
    - `property_type` (enum)
    - `purchase_price` (numeric, min 0)
    - `arv` (numeric, min 0)
    - `transaction_type` (enum)
*   **Optional Fields:**
    - `unit`, `county`, `bedrooms`, `bathrooms`, `square_feet`, `lot_size`, `year_built`, `repair_estimate`, `holding_costs`, `assignment_fee`, `description`, `seller_notes`
*   **Field Restrictions:**
    - `property_type`: Allowed values are `single_family`, `townhouse`, `condo`, `duplex`, `multi_family`, `mobile_home`.
    - `transaction_type`: Allowed values are `assignment`, `double_close`, `wholesale`, `fix_and_flip`, `buy_and_hold`.
*   **Auto-Generated Fields:**
    - `uuid`: Unique identifier automatically generated
    - `user_id`: Set from authenticated user
    - `ai_score`: Random score between 60-100 (mock implementation)
    - `status`: Always set to "draft" for new properties
    - `profit_potential`: Calculated as ARV - purchase_price - repair_estimate - holding_costs
    - `view_count`, `save_count`, `inquiry_count`: All initialized to 0
*   **Response (Success - 201):**

    ```json
    {
        "status": "success",
        "message": "Property created successfully",
        "data": {
            "id": 2,
            "uuid": "550e8400-e29b-41d4-a716-446655440006",
            "user_id": 1,
            "address": "456 Oak Avenue",
            "unit": "Unit A",
            "city": "Austin",
            "state": "TX",
            "zip": "78702",
            "county": "Travis",
            "property_type": "single_family",
            "bedrooms": 3,
            "bathrooms": 2.5,
            "square_feet": 1800,
            "lot_size": 0.25,
            "year_built": 1995,
            "purchase_price": 180000.00,
            "arv": 250000.00,
            "repair_estimate": 25000.00,
            "holding_costs": 5000.00,
            "profit_potential": 40000.00,
            "ai_score": 85,
            "transaction_type": "assignment",
            "assignment_fee": 15000.00,
            "status": "draft",
            "view_count": 0,
            "save_count": 0,
            "inquiry_count": 0,
            "created_at": "2025-06-27T20:00:00.000000Z",
            "updated_at": "2025-06-27T20:00:00.000000Z"
        }
    }
    ```
*   **Validation Error Response (422):**

    ```json
    {
        "status": "error",
        "message": "Validation failed",
        "errors": {
            "address": ["The address field is required."],
            "city": ["The city field is required."],
            "state": ["The state field is required."],
            "zip": ["The zip field is required."],
            "property_type": ["The property type field is required."],
            "purchase_price": ["The purchase price field is required."],
            "arv": ["The arv field is required."],
            "transaction_type": ["The transaction type field is required."]
        }
    }
    ```

### Get Property

*   **Description:** Retrieves a specific property by ID.
*   **Method:** GET
*   **Endpoint:** `/api/properties/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Property retrieved successfully",
        "data": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440002",
            "user_id": 1,
            "address": "456 Oak Avenue",
            "unit": "Unit A",
            "city": "Austin",
            "state": "TX",
            "zip": "78702",
            "county": "Travis",
            "property_type": "single_family",
            "bedrooms": 3,
            "bathrooms": 2.5,
            "square_feet": 1800,
            "lot_size": 0.25,
            "year_built": 1995,
            "purchase_price": 180000.00,
            "arv": 250000.00,
            "repair_estimate": 25000.00,
            "holding_costs": 5000.00,
            "profit_potential": 40000.00,
            "ai_score": 92,
            "transaction_type": "assignment",
            "assignment_fee": 15000.00,
            "status": "active",
            "view_count": 45,
            "save_count": 8,
            "inquiry_count": 3,
            "images": [
                "https://example.com/images/property1_1.jpg",
                "https://example.com/images/property1_2.jpg"
            ],
            "created_at": "2025-06-26T20:00:00.000000Z",
            "updated_at": "2025-06-26T20:00:00.000000Z"
        }
    }
    ```

### Update Property

*   **Description:** Updates an existing property.
*   **Method:** PUT
*   **Endpoint:** `/api/properties/{id}`
*   **Request:**

    ```json
    {
        "address": "456 Oak Avenue",
        "city": "Austin",
        "state": "TX",
        "zip": "78702",
        "purchase_price": 185000.00,
        "arv": 260000.00,
        "status": "active"
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Property updated successfully",
        "data": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440002",
            "user_id": 1,
            "address": "456 Oak Avenue",
            "unit": "Unit A",
            "city": "Austin",
            "state": "TX",
            "zip": "78702",
            "county": "Travis",
            "property_type": "single_family",
            "bedrooms": 3,
            "bathrooms": 2.5,
            "square_feet": 1800,
            "lot_size": 0.25,
            "year_built": 1995,
            "purchase_price": 185000.00,
            "arv": 260000.00,
            "repair_estimate": 25000.00,
            "holding_costs": 5000.00,
            "profit_potential": 45000.00,
            "ai_score": 92,
            "transaction_type": "assignment",
            "assignment_fee": 15000.00,
            "status": "active",
            "view_count": 45,
            "save_count": 8,
            "inquiry_count": 3,
            "images": [
                "https://example.com/images/property1_1.jpg",
                "https://example.com/images/property1_2.jpg"
            ],
            "created_at": "2025-06-26T20:00:00.000000Z",
            "updated_at": "2025-06-27T20:00:00.000000Z"
        }
    }
    ```

### Delete Property

*   **Description:** Deletes a specific property.
*   **Method:** DELETE
*   **Endpoint:** `/api/properties/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Property deleted successfully"
    }
    ```

### Get Property AI Analysis

*   **Description:** Retrieves AI-powered analysis for a specific property.
*   **Method:** GET
*   **Endpoint:** `/api/properties/{property}/ai-analysis`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Property AI analysis retrieved successfully",
        "data": {
            "property_id": 1,
            "ai_score": 92,
            "market_analysis": {
                "comparable_sales": [
                    {"address": "123 Oak Ave", "sale_price": 245000, "date": "2025-05-15"},
                    {"address": "789 Oak Ave", "sale_price": 255000, "date": "2025-04-20"}
                ],
                "market_trends": "Appreciating market with 8% YoY growth",
                "days_on_market_avg": 25
            },
            "repair_analysis": {
                "estimated_repairs": 25000.00,
                "priority_items": ["Roof repair", "HVAC system", "Kitchen updates"],
                "timeline_estimate": "6-8 weeks"
            },
            "investment_metrics": {
                "profit_potential": 40000.00,
                "roi_percentage": 22.2,
                "break_even_price": 210000.00
            }
        }
    }
