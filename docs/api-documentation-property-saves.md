## Property Saves API

### Save Property

*   **Description:** Adds a property to user's saved/favorites list.
*   **Method:** POST
*   **Endpoint:** `/api/property-saves`
*   **Request:**

    ```json
    {
        "property_id": 1
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Property saved successfully",
        "data": {
            "id": 1,
            "user_id": 1,
            "property_id": 1,
            "created_at": "2025-06-27T20:00:00.000000Z",
            "updated_at": "2025-06-27T20:00:00.000000Z"
        }
    }
    ```

### List Saved Properties

*   **Description:** Retrieves user's saved properties.
*   **Method:** GET
*   **Endpoint:** `/api/property-saves`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Saved properties retrieved successfully",
        "data": [
            {
                "id": 1,
                "user_id": 1,
                "property_id": 1,
                "property": {
                    "id": 1,
                    "address": "456 Oak Avenue",
                    "city": "Austin",
                    "state": "TX",
                    "purchase_price": 180000.00,
                    "arv": 250000.00
                },
                "saved_at": "2025-06-26T20:00:00.000000Z"
            }
        ]
    }
    ```

### Remove Saved Property

*   **Description:** Removes a property from user's saved list.
*   **Method:** DELETE
*   **Endpoint:** `/api/property-saves/{property_save}`
*   **Request:**

    ```json
    {
        "property_id": 1
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Property removed from saved list"
    }
    ```

## Property Saves API (Complete)

### List Saved Properties

*   **Description:** Retrieves user's saved properties with pagination.
*   **Method:** GET
*   **Endpoint:** `/api/property-saves`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Saved properties retrieved successfully",
        "data": [
            {
                "id": 1,
                "user_id": 1,
                "property_id": 1,
                "property": {
                    "id": 1,
                    "address": "456 Oak Avenue",
                    "city": "Austin",
                    "state": "TX",
                    "purchase_price": 180000.00,
                    "arv": 250000.00
                },
                "saved_at": "2025-06-26T20:00:00.000000Z"
            }
        ]
    }
    ```

### Save Property

*   **Description:** Adds a property to user's saved/favorites list.
*   **Method:** POST
*   **Endpoint:** `/api/property-saves`
*   **Request:**

    ```json
    {
        "property_id": 1
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Property saved successfully",
        "data": {
            "id": 1,
            "user_id": 1,
            "property_id": 1,
            "created_at": "2025-06-27T20:00:00.000000Z",
            "updated_at": "2025-06-27T20:00:00.000000Z"
        }
    }
    ```

### Get Saved Property

*   **Description:** Retrieves a specific saved property by ID.
*   **Method:** GET
*   **Endpoint:** `/api/property-saves/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Saved property retrieved successfully",
        "data": {
            "id": 1,
            "user_id": 1,
            "property_id": 1,
            "property": {
                "id": 1,
                "address": "456 Oak Avenue",
                "city": "Austin",
                "state": "TX",
                "purchase_price": 180000.00,
                "arv": 250000.00
            },
            "saved_at": "2025-06-26T20:00:00.000000Z"
        }
    }
    ```

### Remove Saved Property

*   **Description:** Removes a property from user's saved list.
*   **Method:** DELETE
*   **Endpoint:** `/api/property-saves/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Property removed from saved list"
    }
