## AI Conversations API

### List AI Conversations

*   **Description:** Retrieves AI conversation history.
*   **Method:** GET
*   **Endpoint:** `/api/ai-conversations`
*   **Query Parameters:**
    - `channel`: Filter by communication channel
    - `status`: Filter by conversation status
    - `lead_id`: Filter by specific lead

*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "AI conversations retrieved successfully",
        "data": [
            {
                "id": 1,
                "uuid": "550e8400-e29b-41d4-a716-446655440004",
                "user_id": 1,
                "lead_id": 1,
                "property_id": 1,
                "channel": "sms",
                "external_id": "twilio_call_123",
                "sentiment_score": 75,
                "urgency_score": 80,
                "motivation_score": 85,
                "qualification_score": 78,
                "extracted_data": {
                    "timeline": "30 days",
                    "motivation": "divorce",
                    "price_flexibility": "moderate"
                },
                "identified_pain_points": ["Financial stress", "Time pressure"],
                "status": "completed",
                "outcome": "qualified_lead",
                "next_steps": "Schedule property visit",
                "created_at": "2025-06-26T20:00:00.000000Z",
                "updated_at": "2025-06-26T20:00:00.000000Z"
            }
        ]
    }
    ```

### Create AI Conversation

*   **Description:** Initiates a new AI conversation.
*   **Method:** POST
*   **Endpoint:** `/api/ai-conversations`
*   **Request:**

    ```json
    {
        "lead_id": 1,
        "property_id": 1,
        "channel": "sms",
        "external_id": "twilio_call_123"
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "AI conversation created successfully",
        "data": {
            "id": 2,
            "uuid": "550e8400-e29b-41d4-a716-446655440010",
            "user_id": 1,
            "lead_id": 1,
            "property_id": 1,
            "channel": "sms",
            "external_id": "twilio_call_123",
            "sentiment_score": 0,
            "urgency_score": 0,
            "motivation_score": 0,
            "qualification_score": 0,
            "extracted_data": null,
            "identified_pain_points": [],
            "status": "initiated",
            "outcome": null,
            "next_steps": null,
            "created_at": "2025-06-27T20:00:00.000000Z",
            "updated_at": "2025-06-27T20:00:00.000000Z"
        }
    }
    ```

### Get AI Conversation

*   **Description:** Retrieves a specific AI conversation by ID.
*   **Method:** GET
*   **Endpoint:** `/api/ai-conversations/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "AI conversation retrieved successfully",
        "data": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440004",
            "user_id": 1,
            "lead_id": 1,
            "property_id": 1,
            "channel": "sms",
            "external_id": "twilio_call_123",
            "sentiment_score": 75,
            "urgency_score": 80,
            "motivation_score": 85,
            "qualification_score": 78,
            "extracted_data": {
                "timeline": "30 days",
                "motivation": "divorce",
                "price_flexibility": "moderate"
            },
            "identified_pain_points": ["Financial stress", "Time pressure"],
            "status": "completed",
            "outcome": "qualified_lead",
            "next_steps": "Schedule property visit",
            "created_at": "2025-06-26T20:00:00.000000Z",
            "updated_at": "2025-06-26T20:00:00.000000Z"
        }
    }
    ```

### Update AI Conversation

*   **Description:** Updates an existing AI conversation.
*   **Method:** PUT
*   **Endpoint:** `/api/ai-conversations/{id}`
*   **Request:**

    ```json
    {
        "sentiment_score": 80,
        "urgency_score": 85,
        "status": "completed",
        "outcome": "qualified_lead",
        "next_steps": "Schedule property visit"
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "AI conversation updated successfully",
        "data": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440004",
            "user_id": 1,
            "lead_id": 1,
            "property_id": 1,
            "channel": "sms",
            "external_id": "twilio_call_123",
            "sentiment_score": 80,
            "urgency_score": 85,
            "motivation_score": 85,
            "qualification_score": 78,
            "extracted_data": {
                "timeline": "30 days",
                "motivation": "divorce",
                "price_flexibility": "moderate"
            },
            "identified_pain_points": ["Financial stress", "Time pressure"],
            "status": "completed",
            "outcome": "qualified_lead",
            "next_steps": "Schedule property visit",
            "created_at": "2025-06-26T20:00:00.000000Z",
            "updated_at": "2025-06-27T20:00:00.000000Z"
        }
    }
    ```

### Delete AI Conversation

*   **Description:** Deletes a specific AI conversation.
*   **Method:** DELETE
*   **Endpoint:** `/api/ai-conversations/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "AI conversation deleted successfully"
    }
