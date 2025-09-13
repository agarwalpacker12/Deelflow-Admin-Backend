<?php

namespace App\Http\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;

class ApiErrorHelper
{
    /**
     * Return a standardized error response
     */
    public static function errorResponse(
        string $message,
        string $errorCode = null,
        array $details = [],
        int $statusCode = Response::HTTP_BAD_REQUEST,
        array $context = []
    ): JsonResponse {
        $response = [
            'status' => 'error',
            'message' => $message,
            'error' => [
                'code' => $errorCode ?? self::generateErrorCode($statusCode),
                'details' => $details,
                'timestamp' => now()->toISOString(),
            ]
        ];

        if (!empty($context)) {
            $response['error']['context'] = $context;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Handle validation errors with detailed field information
     */
    public static function validationError(array $errors, string $operation = 'operation'): JsonResponse
    {
        $fieldErrors = [];
        $suggestions = [];

        foreach ($errors as $field => $messages) {
            $fieldErrors[$field] = $messages;
            
            // Add specific suggestions based on field type
            if (str_contains($field, 'email')) {
                $suggestions[] = "Ensure the email address is in a valid format (e.g., user@example.com)";
            }
            if (str_contains($field, 'password')) {
                $suggestions[] = "Password must be at least 8 characters long and include a mix of letters and numbers";
            }
            if (str_contains($field, 'phone')) {
                $suggestions[] = "Phone number should be in a valid format with area code";
            }
            if (str_contains($field, 'date')) {
                $suggestions[] = "Date should be in YYYY-MM-DD format";
            }
            if (str_contains($field, 'price') || str_contains($field, 'amount')) {
                $suggestions[] = "Amount should be a positive number without currency symbols";
            }
        }

        return self::errorResponse(
            "Validation failed for {$operation}. Please check the provided data and correct the errors.",
            'VALIDATION_ERROR',
            [
                'failed_fields' => array_keys($fieldErrors),
                'field_errors' => $fieldErrors,
                'suggestions' => array_unique($suggestions),
                'total_errors' => count($fieldErrors)
            ],
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * Handle model not found errors with specific resource information
     */
    public static function notFoundError(string $resourceType, $resourceId = null, array $searchCriteria = []): JsonResponse
    {
        $message = $resourceId 
            ? "The {$resourceType} with ID '{$resourceId}' was not found or you don't have permission to access it."
            : "The requested {$resourceType} was not found or you don't have permission to access it.";

        $details = [
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'possible_reasons' => [
                'The resource may have been deleted',
                'You may not have permission to access this resource',
                'The ID may be incorrect or invalid'
            ]
        ];

        if (!empty($searchCriteria)) {
            $details['search_criteria'] = $searchCriteria;
        }

        return self::errorResponse(
            $message,
            'RESOURCE_NOT_FOUND',
            $details,
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Handle database constraint errors with specific guidance
     */
    public static function databaseError(QueryException $e, string $operation = 'operation', string $resourceType = 'resource'): JsonResponse
    {
        $errorCode = $e->getCode();
        $sqlMessage = $e->getMessage();
        $message = "Database error occurred during {$operation}.";
        $details = [];
        $suggestions = [];

        // Handle specific database errors
        if (str_contains($sqlMessage, 'Duplicate entry')) {
            $message = "A {$resourceType} with this information already exists.";
            $details['constraint_type'] = 'unique_violation';
            $suggestions[] = 'Please use different values for unique fields like email, phone, or reference numbers';
            $suggestions[] = 'Check if a similar record already exists before creating a new one';
            
            // Extract which field caused the duplicate
            if (preg_match("/Duplicate entry '(.+)' for key '(.+)'/", $sqlMessage, $matches)) {
                $details['duplicate_value'] = $matches[1];
                $details['duplicate_field'] = $matches[2];
            }
        } elseif (str_contains($sqlMessage, 'foreign key constraint')) {
            $message = "Cannot complete {$operation} due to related data constraints.";
            $details['constraint_type'] = 'foreign_key_violation';
            $suggestions[] = 'Ensure all referenced records exist (e.g., user_id, property_id)';
            $suggestions[] = 'Check that related resources have not been deleted';
            
            // Extract foreign key information
            if (preg_match("/FOREIGN KEY \(`(.+)`\) REFERENCES `(.+)` \(`(.+)`\)/", $sqlMessage, $matches)) {
                $details['foreign_key_field'] = $matches[1];
                $details['referenced_table'] = $matches[2];
                $details['referenced_field'] = $matches[3];
            }
        } elseif (str_contains($sqlMessage, 'cannot be null') || str_contains($sqlMessage, 'Column') && str_contains($sqlMessage, 'cannot be null')) {
            $message = "Required fields are missing for {$operation}.";
            $details['constraint_type'] = 'not_null_violation';
            $suggestions[] = 'Please provide all required fields';
            $suggestions[] = 'Check the API documentation for required field specifications';
            
            // Extract which column cannot be null
            if (preg_match("/Column '(.+)' cannot be null/", $sqlMessage, $matches)) {
                $details['missing_field'] = $matches[1];
                $suggestions[] = "The field '{$matches[1]}' is required and cannot be empty";
            }
        } elseif (str_contains($sqlMessage, 'Data too long')) {
            $message = "Data provided exceeds maximum allowed length.";
            $details['constraint_type'] = 'data_length_violation';
            $suggestions[] = 'Reduce the length of text fields';
            $suggestions[] = 'Check field length limits in the API documentation';
        } elseif (str_contains($sqlMessage, 'Out of range')) {
            $message = "Numeric value is out of acceptable range.";
            $details['constraint_type'] = 'numeric_range_violation';
            $suggestions[] = 'Ensure numeric values are within acceptable limits';
            $suggestions[] = 'Check for negative values where only positive numbers are allowed';
        }

        return self::errorResponse(
            $message,
            'DATABASE_ERROR',
            array_merge($details, [
                'suggestions' => $suggestions,
                'operation' => $operation,
                'resource_type' => $resourceType
            ]),
            Response::HTTP_CONFLICT,
            ['sql_error_code' => $errorCode]
        );
    }

    /**
     * Handle authorization errors with specific context
     */
    public static function unauthorizedError(string $action = 'perform this action', array $context = []): JsonResponse
    {
        $details = [
            'required_action' => $action,
            'possible_solutions' => [
                'Ensure you are logged in with a valid token',
                'Check if your session has expired',
                'Verify you have the correct permissions for this action'
            ]
        ];

        if (!empty($context)) {
            $details['context'] = $context;
        }

        return self::errorResponse(
            "You are not authorized to {$action}.",
            'UNAUTHORIZED_ACCESS',
            $details,
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Handle forbidden errors with role/permission context
     */
    public static function forbiddenError(string $action = 'perform this action', string $requiredRole = null, array $context = []): JsonResponse
    {
        $message = "You don't have permission to {$action}.";
        $details = [
            'required_action' => $action,
            'possible_reasons' => [
                'Your account role may not have sufficient privileges',
                'The resource may belong to another user',
                'Your account may be inactive or suspended'
            ]
        ];

        if ($requiredRole) {
            $details['required_role'] = $requiredRole;
            $details['possible_reasons'][] = "This action requires '{$requiredRole}' role or higher";
        }

        if (!empty($context)) {
            $details['context'] = $context;
        }

        return self::errorResponse(
            $message,
            'FORBIDDEN_ACCESS',
            $details,
            Response::HTTP_FORBIDDEN
        );
    }

    /**
     * Handle server errors with debugging information
     */
    public static function serverError(string $operation = 'operation', \Exception $exception = null, array $context = []): JsonResponse
    {
        $details = [
            'operation' => $operation,
            'suggestions' => [
                'Please try again in a few moments',
                'If the problem persists, contact support',
                'Check if all required services are running'
            ]
        ];

        if ($exception && config('app.debug')) {
            $details['debug_info'] = [
                'exception_type' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'message' => $exception->getMessage()
            ];
        }

        if (!empty($context)) {
            $details['context'] = $context;
        }

        return self::errorResponse(
            "An internal server error occurred during {$operation}. Our team has been notified.",
            'INTERNAL_SERVER_ERROR',
            $details,
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * Handle business logic errors
     */
    public static function businessLogicError(string $message, string $errorCode, array $details = [], array $suggestions = []): JsonResponse
    {
        $errorDetails = array_merge($details, [
            'suggestions' => $suggestions
        ]);

        return self::errorResponse(
            $message,
            $errorCode,
            $errorDetails,
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Handle rate limiting errors
     */
    public static function rateLimitError(int $retryAfter = 60): JsonResponse
    {
        return self::errorResponse(
            'Too many requests. Please slow down and try again later.',
            'RATE_LIMIT_EXCEEDED',
            [
                'retry_after_seconds' => $retryAfter,
                'suggestions' => [
                    'Wait before making another request',
                    'Consider implementing request throttling in your application',
                    'Contact support if you need higher rate limits'
                ]
            ],
            Response::HTTP_TOO_MANY_REQUESTS
        );
    }

    /**
     * Generate error code based on status code
     */
    private static function generateErrorCode(int $statusCode): string
    {
        $codes = [
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            409 => 'CONFLICT',
            422 => 'VALIDATION_ERROR',
            429 => 'RATE_LIMIT_EXCEEDED',
            500 => 'INTERNAL_SERVER_ERROR'
        ];

        return $codes[$statusCode] ?? 'UNKNOWN_ERROR';
    }
}
