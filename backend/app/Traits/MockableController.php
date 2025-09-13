<?php

namespace App\Traits;

use App\Services\MockDataService;
use App\Http\Helpers\ApiErrorHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;

trait MockableController
{
    protected $mockDataService;

    protected function initializeMockDataService()
    {
        if (config('mockdata.enabled')) {
            $this->mockDataService = new MockDataService();
        }
    }

    protected function isMockEnabled(): bool
    {
        return config('mockdata.enabled', false);
    }

    protected function successResponse($data, $message = 'Operation successful', $statusCode = 200): JsonResponse
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ];

        // Add rate limiting headers
        $response = response()->json($response, $statusCode);
        $this->addRateLimitHeaders($response);
        
        return $response;
    }

    protected function validationErrorResponse($errors, string $operation = 'data validation'): JsonResponse
    {
        return ApiErrorHelper::validationError($errors->all(), $operation);
    }

    protected function notFoundResponse(string $resourceType = 'resource', $resourceId = null): JsonResponse
    {
        return ApiErrorHelper::notFoundError($resourceType, $resourceId);
    }

    protected function unauthorizedResponse(string $action = 'perform this action'): JsonResponse
    {
        return ApiErrorHelper::unauthorizedError($action);
    }

    protected function forbiddenResponse(string $action = 'perform this action', string $requiredRole = null): JsonResponse
    {
        return ApiErrorHelper::forbiddenError($action, $requiredRole);
    }

    protected function serverErrorResponse(string $operation = 'operation', \Exception $exception = null): JsonResponse
    {
        return ApiErrorHelper::serverError($operation, $exception);
    }

    protected function databaseErrorResponse(QueryException $e, string $operation = 'operation', string $resourceType = 'resource'): JsonResponse
    {
        return ApiErrorHelper::databaseError($e, $operation, $resourceType);
    }

    protected function businessLogicErrorResponse(string $message, string $errorCode, array $details = [], array $suggestions = []): JsonResponse
    {
        return ApiErrorHelper::businessLogicError($message, $errorCode, $details, $suggestions);
    }

    protected function addRateLimitHeaders($response)
    {
        $limit = auth()->check() 
            ? config('mockdata.rate_limiting.authenticated_limit', 1000)
            : config('mockdata.rate_limiting.unauthenticated_limit', 100);
        
        $remaining = $limit - 1; // Mock remaining requests
        $reset = now()->addHour()->timestamp;

        $response->headers->set('X-RateLimit-Limit', $limit);
        $response->headers->set('X-RateLimit-Remaining', $remaining);
        $response->headers->set('X-RateLimit-Reset', $reset);

        return $response;
    }

    protected function getPaginationParams($request)
    {
        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(
            config('mockdata.pagination.max_per_page', 100),
            max(1, (int) $request->get('per_page', config('mockdata.pagination.default_per_page', 10)))
        );

        return [$page, $perPage];
    }

    protected function getFilterParams($request, $allowedFilters = [])
    {
        $filters = [];
        
        foreach ($allowedFilters as $filter) {
            if ($request->has($filter)) {
                $filters[$filter] = $request->get($filter);
            }
        }

        // Add common filters
        if ($request->has('search')) {
            $filters['search'] = $request->get('search');
        }

        if ($request->has('status')) {
            $filters['status'] = $request->get('status');
        }

        if ($request->has('created_from')) {
            $filters['created_from'] = $request->get('created_from');
        }

        if ($request->has('created_to')) {
            $filters['created_to'] = $request->get('created_to');
        }

        return $filters;
    }

    protected function formatPaginatedResponse($paginatedData, $message = 'Data retrieved successfully')
    {
        return [
            'status' => 'success',
            'message' => $message,
            'data' => $paginatedData['data'],
            'meta' => $paginatedData['meta'],
            'links' => $paginatedData['links'] ?? null
        ];
    }

    protected function generateMockToken($user = null): string
    {
        if (!$user) {
            $user = ['id' => 1, 'email' => 'mock@example.com'];
        }
        
        // Generate a mock token for development
        return base64_encode(json_encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'expires_at' => now()->addDays(30)->timestamp,
            'mock' => true
        ]));
    }

    protected function validateMockToken($token): ?array
    {
        try {
            $decoded = json_decode(base64_decode($token), true);
            
            if (!$decoded || !isset($decoded['mock']) || !$decoded['mock']) {
                return null;
            }

            if ($decoded['expires_at'] < now()->timestamp) {
                return null;
            }

            return $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getMockUser($userId = 1): ?array
    {
        if ($this->isMockEnabled()) {
            $this->initializeMockDataService();
            return $this->mockDataService->getUser($userId);
        }
        
        return null;
    }

    protected function createMockUser($data): array
    {
        if ($this->isMockEnabled()) {
            $this->initializeMockDataService();
            return $this->mockDataService->createUser($data);
        }
        
        throw new \Exception('Mock data is not enabled');
    }

    protected function authenticateMockUser($email, $password): ?array
    {
        if (!$this->isMockEnabled()) {
            return null;
        }

        $this->initializeMockDataService();
        
        // Simple mock authentication - in real implementation, check password hash
        $users = $this->mockDataService->getUsers(['email' => $email]);
        
        if (!empty($users['data'])) {
            $user = $users['data'][0];
            return [
                'user' => $user,
                'token' => $this->generateMockToken($user)
            ];
        }

        return null;
    }

    protected function getCurrentMockUser($request): ?array
    {
        if (!$this->isMockEnabled()) {
            return null;
        }

        $token = $request->bearerToken();
        if (!$token) {
            return null;
        }

        $tokenData = $this->validateMockToken($token);
        if (!$tokenData) {
            return null;
        }

        return $this->getMockUser($tokenData['user_id']);
    }
}
