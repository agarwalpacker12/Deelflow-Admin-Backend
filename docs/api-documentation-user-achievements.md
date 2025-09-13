## User Achievements API

### List User Achievements

*   **Description:** Retrieves user's achievements and points.
*   **Method:** GET
*   **Endpoint:** `/api/user-achievements`

*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "User achievements retrieved successfully",
        "data": [
            {
                "id": 1,
                "user_id": 1,
                "achievement_type": "deal_milestone",
                "achievement_name": "First Deal Closed",
                "points_earned": 100,
                "metadata": {
                    "deal_id": 1,
                    "deal_value": 15000.00
                },
                "earned_at": "2025-06-26T20:00:00.000000Z"
            }
        ],
        "summary": {
            "total_points": 350,
            "current_level": 2,
            "points_to_next_level": 150,
            "total_achievements": 5
        }
    }
    ```

### Create User Achievement

*   **Description:** Creates a new achievement (typically called by system).
*   **Method:** POST
*   **Endpoint:** `/api/user-achievements`
*   **Request:**

    ```json
    {
        "achievement_type": "deal_milestone",
        "achievement_name": "First Deal Closed",
        "points_earned": 100,
        "metadata": {
            "deal_id": 1,
            "deal_value": 15000.00
        }
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "User achievement created successfully",
        "data": {
            "id": 2,
            "user_id": 1,
            "achievement_type": "deal_milestone",
            "achievement_name": "First Deal Closed",
            "points_earned": 100,
            "metadata": {
                "deal_id": 1,
                "deal_value": 15000.00
            },
            "earned_at": "2025-06-27T20:00:00.000000Z"
        }
    }
    ```

### Get User Achievement

*   **Description:** Retrieves a specific achievement by ID.
*   **Method:** GET
*   **Endpoint:** `/api/user-achievements/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "User achievement retrieved successfully",
        "data": {
            "id": 1,
            "user_id": 1,
            "achievement_type": "deal_milestone",
            "achievement_name": "First Deal Closed",
            "points_earned": 100,
            "metadata": {
                "deal_id": 1,
                "deal_value": 15000.00
            },
            "earned_at": "2025-06-26T20:00:00.000000Z"
        }
    }
    ```

### Delete User Achievement

*   **Description:** Deletes a specific achievement (admin only operation).
*   **Method:** DELETE
*   **Endpoint:** `/api/user-achievements/{id}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "User achievement deleted successfully"
    }
