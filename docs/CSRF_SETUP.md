# CSRF Token Configuration for Laravel + React

This document explains the CSRF (Cross-Site Request Forgery) protection setup between the Laravel backend and React frontend.

## Problem

Laravel's Sanctum middleware requires CSRF tokens for stateful authentication, but the React frontend was not properly configured to handle these tokens, resulting in "CSRF token mismatch" errors.

## Solution Overview

The solution involves configuring both the Laravel backend and React frontend to properly handle CSRF tokens using Laravel Sanctum's stateful authentication.

## Backend Configuration

### 1. Laravel Routes (`backend/routes/web.php`)
Added the CSRF cookie endpoint:
```php
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});
```

### 2. CORS Configuration (`backend/config/cors.php`)
- `paths` includes `'sanctum/csrf-cookie'`
- `supports_credentials` is set to `true`

### 3. Sanctum Configuration (`backend/config/sanctum.php`)
- `stateful` domains include frontend domains (localhost:5173, etc.)
- Middleware includes CSRF validation

### 4. Environment Variables (`backend/.env`)
```env
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173,https://localhost:5173,https://127.0.0.1:5173
SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173
SESSION_DOMAIN=localhost
```

## Frontend Configuration

### 1. Axios Configuration (`frontend/src/services/api.js`)

#### Key Changes:
- **withCredentials: true** - Ensures cookies are sent with requests
- **X-Requested-With: XMLHttpRequest** - Required for Laravel to recognize AJAX requests
- **CSRF token extraction** from cookies
- **Automatic CSRF token inclusion** in request headers
- **419 error handling** with automatic retry

#### CSRF Token Functions:
```javascript
// Function to get CSRF token from cookies
const getCsrfToken = () => {
  const name = 'XSRF-TOKEN';
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) {
    return decodeURIComponent(parts.pop().split(';').shift());
  }
  return null;
};

// Function to fetch CSRF cookie
const fetchCsrfCookie = async () => {
  try {
    await axios.get(`${BASE_URL}/sanctum/csrf-cookie`, {
      withCredentials: true,
    });
  } catch (error) {
    console.error('Failed to fetch CSRF cookie:', error);
  }
};
```

#### Request Interceptor:
- Automatically adds CSRF token to state-changing requests (POST, PUT, PATCH, DELETE)
- Fetches CSRF cookie if token is not available

#### Response Interceptor:
- Handles 419 CSRF token mismatch errors
- Automatically refreshes token and retries the request

### 2. App Initialization (`frontend/src/App.jsx`)
- Initializes CSRF cookie on app startup
- Includes CSRF testing in development mode

### 3. CSRF Testing Utility (`frontend/src/utils/csrfTest.js`)
Provides testing functionality to verify CSRF setup is working correctly.

## How It Works

1. **App Initialization**: When the React app starts, it fetches the CSRF cookie from Laravel
2. **Request Processing**: For state-changing requests, the Axios interceptor:
   - Checks if a CSRF token exists in cookies
   - If not, fetches the CSRF cookie first
   - Adds the token to the `X-XSRF-TOKEN` header
3. **Error Handling**: If a 419 CSRF error occurs:
   - Automatically refreshes the CSRF token
   - Retries the original request
4. **Cookie Management**: All requests include `withCredentials: true` to ensure cookies are sent

## Key Headers

- **X-Requested-With: XMLHttpRequest** - Identifies the request as AJAX
- **X-XSRF-TOKEN** - Contains the CSRF token value
- **withCredentials: true** - Ensures cookies are included in requests

## Testing

The setup includes a test utility that verifies:
- CSRF cookie can be fetched
- Token is properly extracted from cookies
- Token is available for use in requests

## Troubleshooting

### Common Issues:

1. **Domain Mismatch**: Ensure `SANCTUM_STATEFUL_DOMAINS` includes your frontend domain
2. **CORS Issues**: Verify `CORS_ALLOWED_ORIGINS` includes your frontend URL
3. **Cookie Issues**: Check that `withCredentials: true` is set on all requests
4. **Session Domain**: Ensure `SESSION_DOMAIN` matches your setup

### Debug Steps:

1. Check browser developer tools for CSRF cookies
2. Verify request headers include `X-XSRF-TOKEN`
3. Check console logs for CSRF initialization messages
4. Use the built-in CSRF test utility in development mode

## Security Notes

- CSRF tokens are automatically managed and rotated by Laravel
- Tokens are tied to the user's session
- The setup only works for configured stateful domains
- All state-changing requests are protected by CSRF validation

This configuration provides robust CSRF protection while maintaining a smooth user experience with automatic token management and error recovery.
