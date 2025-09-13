# Unified Server Setup Instructions

This project now uses a unified server architecture where the backend Vite server acts as the main entry point and proxies frontend requests.

## Architecture Overview

- **Backend Vite Server** (Port 5174): Main entry point that handles:
  - Laravel API routes (`/api/*`)
  - Proxies all other requests to the frontend
  - Laravel asset compilation (CSS/JS)

- **Frontend Vite Server** (Port 5173): React development server
  - Serves the React application
  - Hot module replacement for frontend development

## How to Run

### Option 1: Run Both Servers Simultaneously (Recommended)

From the `backend` directory:
```bash
cd backend
npm run dev:full
```

This will start both the frontend and backend Vite servers concurrently.

### Option 2: Run Servers Separately

**Terminal 1 - Frontend:**
```bash
cd frontend
npm run dev
```

**Terminal 2 - Backend:**
```bash
cd backend
npm run dev
```

## Access Points

- **Main Application**: http://localhost:5174 (Backend Vite server)
  - All frontend routes (/, /dashboard, /leads, etc.) → Proxied to frontend
  - API routes (/api/*) → Handled by Laravel backend

- **Direct Frontend**: http://localhost:5173 (Frontend Vite server)
  - Direct access to React app (for frontend-only development)

## How It Works

1. **Frontend Requests**: When you visit http://localhost:5174, any non-API request is proxied to the frontend Vite server at port 5173
2. **API Requests**: Requests to `/api/*` are handled directly by the Laravel backend
3. **Hot Reloading**: Both frontend and backend support hot module replacement
4. **WebSocket Support**: WebSocket connections for HMR are properly proxied

## Benefits

- **Single Entry Point**: Access your entire application through one URL
- **Simplified Development**: No need to remember multiple ports
- **Production-Like Setup**: Mimics how the application would work in production
- **API Integration**: Seamless communication between frontend and backend
- **Hot Reloading**: Full development experience maintained

## Configuration Files

- `backend/vite.config.js`: Configured with proxy rules for non-API requests
- `frontend/vite.config.js`: Simplified configuration without proxy (since backend handles routing)
- `backend/package.json`: Added scripts for running both servers

## Troubleshooting

1. **Port Conflicts**: Ensure ports 5173 and 5174 are available
2. **Proxy Issues**: Check that frontend server is running on port 5173 before starting backend
3. **API Calls**: Frontend API calls use relative paths (`/api`) which work with the proxy setup

## GitHub Codespaces Specific Notes

When running in GitHub Codespaces:

1. **WebSocket Warnings**: You may see WebSocket connection warnings in the browser console. This is normal in Codespaces and doesn't affect functionality.

2. **Hot Module Replacement**: HMR may be limited due to WebSocket restrictions in Codespaces, but the application will still work correctly.

3. **Manifest.json**: A basic manifest.json file is included to prevent CORS errors.

4. **Host Configuration**: Both servers are configured with `host: '0.0.0.0'` to allow external connections required by Codespaces.

5. **Access URLs**: Use the Codespaces-provided URLs (e.g., `https://automatic-disco-x5xw4x5wqjqqhpr45-5174.app.github.dev/`) instead of localhost URLs when accessing from outside the Codespace.
