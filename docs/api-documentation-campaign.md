## Campaigns API

### List Campaigns

*   **Description:** Retrieves marketing campaigns with performance metrics.
*   **Method:** GET
*   **Endpoint:** `/api/campaigns`

*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Campaigns retrieved successfully",
        "data": [
            {
                "id": 1,
                "user_id": 1,
                "name": "Austin Distressed Properties Q3",
                "campaign_type": "seller_finder",
                "channel": "email",
                "target_criteria": {
                    "location": "Austin, TX",
                    "property_type": "single_family",
                    "equity_min": 50000
                },
                "subject_line": "We Buy Houses Fast - Cash Offer in 24 Hours",
                "status": "active",
                "scheduled_at": "2025-06-27T09:00:00.000000Z",
                "total_recipients": 500,
                "sent_count": 450,
                "open_count": 135,
                "click_count": 45,
                "response_count": 12,
                "conversion_count": 3,
                "budget": 1000.00,
                "spent": 750.00,
                "created_at": "2025-06-26T20:00:00.000000Z",
                "updated_at": "2025-06-26T20:00:00.000000Z"
            }
        ]
    }
    ```

### Create Campaign

*   **Description:** Creates a new marketing campaign.
*   **Method:** POST
*   **Endpoint:** `/api/campaigns`
*   **Request:**

    ```json
    {
        "name": "Austin Distressed Properties Q3",
        "campaign_type": "seller_finder",
        "channel": "email",
        "target_criteria": {
            "location": "Austin, TX",
            "property_type": "single_family",
            "equity_min": 50000
        },
        "subject_line": "We Buy Houses Fast - Cash Offer in 24 Hours",
        "email_content": "Hello [FIRST_NAME], we specialize in buying houses...",
        "scheduled_at": "2025-06-27T09:00:00.000000Z",
        "budget": 1000.00,
        "use_ai_personalization": true
    }
    ```
*   **Field Restrictions:**
    - `campaign_type`: Allowed values are `seller_finder`, `buyer_finder`.
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Campaign created successfully",
        "data": {
            "id": 2,
            "user_id": 1,
            "name": "Austin Distressed Properties Q3",
            "campaign_type": "seller_finder",
            "channel": "email",
            "target_criteria": {
                "location": "Austin, TX",
                "property_type": "single_family",
                "equity_min": 50000
            },
            "subject_line": "We Buy Houses Fast - Cash Offer in 24 Hours",
            "status": "draft",
            "scheduled_at": "2025-06-27T09:00:00.000000Z",
            "total_recipients": 0,
            "sent_count": 0,
            "open_count": 0,
            "click_count": 0,
            "response_count": 0,
            "conversion_count": 0,
            "budget": 1000.00,
            "spent": 0.00,
            "created_at": "2025-06-27T20:00:00.000000Z",
            "updated_at": "2025-06-27T20:00:00.000000Z"
        }
    }
    ```

### Get Campaign

*   **Description:** Retrieves a specific campaign by ID.
*   **Method:** GET
*   **Endpoint:** `/api/campaigns/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Campaign retrieved successfully",
        "data": {
            "id": 1,
            "user_id": 1,
            "name": "Austin Distressed Properties Q3",
            "campaign_type": "seller_finder",
            "channel": "email",
            "target_criteria": {
                "location": "Austin, TX",
                "property_type": "single_family",
                "equity_min": 50000
            },
            "subject_line": "We Buy Houses Fast - Cash Offer in 24 Hours",
            "status": "active",
            "scheduled_at": "2025-06-27T09:00:00.000000Z",
            "total_recipients": 500,
            "sent_count": 450,
            "open_count": 135,
            "click_count": 45,
            "response_count": 12,
            "conversion_count": 3,
            "budget": 1000.00,
            "spent": 750.00,
            "created_at": "2025-06-26T20:00:00.000000Z",
            "updated_at": "2025-06-26T20:00:00.000000Z"
        }
    }
    ```

### Update Campaign

*   **Description:** Updates an existing campaign.
*   **Method:** PUT
*   **Endpoint:** `/api/campaigns/{id}`
*   **Request:**

    ```json
    {
        "name": "Updated Campaign Name",
        "status": "paused",
        "budget": 1500.00
    }
    ```
*   **Field Restrictions:**
    - `status`: Allowed values are `draft`, `scheduled`, `active`, `paused`, `completed`, `cancelled`.
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Campaign updated successfully",
        "data": {
            "id": 1,
            "user_id": 1,
            "name": "Updated Campaign Name",
            "campaign_type": "seller_finder",
            "channel": "email",
            "target_criteria": {
                "location": "Austin, TX",
                "property_type": "single_family",
                "equity_min": 50000
            },
            "subject_line": "We Buy Houses Fast - Cash Offer in 24 Hours",
            "status": "paused",
            "scheduled_at": "2025-06-27T09:00:00.000000Z",
            "total_recipients": 500,
            "sent_count": 450,
            "open_count": 135,
            "click_count": 45,
            "response_count": 12,
            "conversion_count": 3,
            "budget": 1500.00,
            "spent": 750.00,
            "created_at": "2025-06-26T20:00:00.000000Z",
            "updated_at": "2025-06-27T20:00:00.000000Z"
        }
    }
    ```

### Delete Campaign

*   **Description:** Deletes a specific campaign.
*   **Method:** DELETE
*   **Endpoint:** `/api/campaigns/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Campaign deleted successfully"
    }
    ```

### Get Campaign Recipients

*   **Description:** Retrieves recipients for a specific campaign.
*   **Method:** GET
*   **Endpoint:** `/api/campaigns/{id}/recipients`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Campaign recipients retrieved successfully",
        "data": [
            {
                "id": 1,
                "campaign_id": 1,
                "lead_id": 1,
                "sent_at": "2025-06-27T10:00:00.000000Z",
                "opened_at": "2025-06-27T10:15:00.000000Z",
                "clicked_at": "2025-06-27T10:20:00.000000Z",
                "responded_at": null,
                "open_count": 2,
                "click_count": 1,
                "response_count": 0,
                "created_at": "2025-06-27T20:00:00.000000Z",
                "updated_at": "2025-06-27T20:00:00.000000Z"
            }
        ]
    }
    ```

## Campaign Recipients API

### List Campaign Recipients

*   **Description:** Retrieves recipients and their engagement for a campaign.
*   **Method:** GET
*   **Endpoint:** `/api/campaigns/{campaign}/recipients`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Campaign recipients retrieved successfully",
        "data": [
            {
                "id": 1,
                "campaign_id": 1,
                "lead_id": 1,
                "sent_at": "2025-06-27T10:00:00.000000Z",
                "opened_at": "2025-06-27T10:15:00.000000Z",
                "clicked_at": "2025-06-27T10:20:00.000000Z",
                "responded_at": null,
                "open_count": 2,
                "click_count": 1,
                "response_count": 0,
                "created_at": "2025-06-27T20:00:00.000000Z",
                "updated_at": "2025-06-27T20:00:00.000000Z"
            }
        ]
    }
    ```

### Add Campaign Recipients

*   **Description:** Adds recipients to a campaign.
*   **Method:** POST
*   **Endpoint:** `/api/campaign-recipients`
*   **Request:**

    ```json
    {
        "campaign_id": 1,
        "lead_ids": [1, 2, 3, 4, 5]
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Campaign recipients added successfully"
    }
    ```

### List All Campaign Recipients

*   **Description:** Retrieves all campaign recipients with filtering options.
*   **Method:** GET
*   **Endpoint:** `/api/campaign-recipients`
*   **Query Parameters:**
    - `campaign_id`: Filter by specific campaign
    - `sent`: Filter by sent status
    - `opened`: Filter by opened status
    - `clicked`: Filter by clicked status
    - `responded`: Filter by responded status
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "All campaign recipients retrieved successfully",
        "data": [
            {
                "id": 1,
                "campaign_id": 1,
                "lead_id": 1,
                "sent_at": "2025-06-27T10:00:00.000000Z",
                "opened_at": "2025-06-27T10:15:00.000000Z",
                "clicked_at": "2025-06-27T10:20:00.000000Z",
                "responded_at": null,
                "open_count": 2,
                "click_count": 1,
                "response_count": 0,
                "created_at": "2025-06-27T20:00:00.000000Z",
                "updated_at": "2025-06-27T20:00:00.000000Z"
            }
        ]
    }
    ```

### Get Campaign Recipient

*   **Description:** Retrieves a specific campaign recipient by ID.
*   **Method:** GET
*   **Endpoint:** `/api/campaign-recipients/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Campaign recipient retrieved successfully",
        "data": {
            "id": 1,
            "campaign_id": 1,
            "lead_id": 1,
            "sent_at": "2025-06-27T10:00:00.000000Z",
            "opened_at": "2025-06-27T10:15:00.000000Z",
            "clicked_at": "2025-06-27T10:20:00.000000Z",
            "responded_at": null,
            "open_count": 2,
            "click_count": 1,
            "response_count": 0,
            "created_at": "2025-06-27T20:00:00.000000Z",
            "updated_at": "2025-06-27T20:00:00.000000Z"
        }
    }
    ```

### Update Campaign Recipient

*   **Description:** Updates campaign recipient engagement tracking.
*   **Method:** PUT
*   **Endpoint:** `/api/campaign-recipients/{id}`
*   **Request:**

    ```json
    {
        "sent_at": "2025-06-27T10:00:00.000000Z",
        "opened_at": "2025-06-27T10:15:00.000000Z",
        "clicked_at": "2025-06-27T10:20:00.000000Z",
        "open_count": 2,
        "click_count": 1
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Campaign recipient updated successfully",
        "data": {
            "id": 1,
            "campaign_id": 1,
            "lead_id": 1,
            "sent_at": "2025-06-27T10:00:00.000000Z",
            "opened_at": "2025-06-27T10:15:00.000000Z",
            "clicked_at": "2025-06-27T10:20:00.000000Z",
            "responded_at": null,
            "open_count": 2,
            "click_count": 1,
            "response_count": 0,
            "created_at": "2025-06-27T20:00:00.000000Z",
            "updated_at": "2025-06-28T12:00:00.000000Z"
        }
    }
    ```

### Remove Campaign Recipient

*   **Description:** Removes a recipient from a campaign.
*   **Method:** DELETE
*   **Endpoint:** `/api/campaign-recipients/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Campaign recipient removed successfully"
    }
