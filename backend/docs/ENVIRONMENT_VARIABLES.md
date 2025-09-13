# Environment Variables

This document describes the environment variables used in the Laravel backend application.

## Port Configuration

### APP_PORT

Controls the port on which the Laravel application server runs.

- **Default**: `8000`
- **Example**: `APP_PORT=3001`
- **Usage**: Set this in your `.env` file to change the port

#### How it works:

1. **Backend**: The `start-dev.sh` script reads the `APP_PORT` value from the `.env` file
2. **Frontend**: The frontend can be configured to connect to the backend on a different port using `REACT_APP_API_PORT`

#### Examples:

**To run the backend on port 3001:**

1. Update `backend/.env`:
   ```
   APP_PORT=3001
   ```

2. Update `frontend/.env` (if you have one) or set environment variable:
   ```
   REACT_APP_API_PORT=3001
   ```

3. Run the development servers:
   ```bash
   ./start-dev.sh
   ```

**Manual Laravel server start:**
```bash
cd backend
php artisan serve --port=3001 --host=0.0.0.0
```

## Other Environment Variables

See `.env.example` for a complete list of available environment variables and their default values.

## Notes

- The `start-dev.sh` script automatically reads the `APP_PORT` from the backend `.env` file
- If `APP_PORT` is not set, it defaults to `8000`
- The frontend will automatically connect to the correct port if `REACT_APP_API_PORT` is set
- For production deployments, ensure your deployment platform supports the configured port
