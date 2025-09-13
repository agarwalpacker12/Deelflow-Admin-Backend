#!/bin/bash

# Unified Development Server Startup Script
# This script starts both frontend and backend Vite servers

echo "🚀 Starting Dealflow Development Servers..."
echo ""

# Function to kill processes on a given port
kill_on_port() {
  PORT=$1
  PIDS=$(lsof -t -i:$PORT 2>/dev/null)
  if [ -n "$PIDS" ]; then
    echo "Stopping process(es) on port $PORT..."
    kill -9 $PIDS
  fi
}

# Stop all potentially running web server processes
stop_all_web_servers() {
    echo "🛑 Stopping any running web servers..."
    
    # Load environment variables to get the backend port
    if [ -f backend/.env ]; then
        APP_PORT=$(grep "^APP_PORT=" backend/.env | cut -d '=' -f2)
    fi
    APP_PORT=${APP_PORT:-8000}

    # List of ports to check and clear
    PORTS_TO_KILL="5173 5174 $APP_PORT 3000 3001 8080"

    for PORT in $PORTS_TO_KILL; do
        kill_on_port $PORT
    done
    
    echo "All specified ports have been cleared."
}

# Check if we're in the right directory
if [ ! -d "backend" ] || [ ! -d "frontend" ]; then
    echo "❌ Error: Please run this script from the project root directory"
    echo "   Make sure both 'backend' and 'frontend' directories exist"
    exit 1
fi

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo "❌ Error: npm is not installed"
    echo "   Please install Node.js and npm first"
    exit 1
fi

# Stop servers first
stop_all_web_servers

# Install dependencies if node_modules don't exist
if [ ! -d "backend/node_modules" ]; then
    echo "📦 Installing backend dependencies..."
    (cd backend && npm install)
fi

if [ ! -d "frontend/node_modules" ]; then
    echo "📦 Installing frontend dependencies..."
    (cd frontend && npm install)
fi

echo ""
# Install proxy server dependencies if node_modules don't exist
if [ ! -d "node_modules" ]; then
    echo "📦 Installing proxy server dependencies..."
    npm install
fi

echo "🎯 Starting servers..."
echo "   Proxy Server: http://localhost:3000 (Main application entry point)"
echo "   Frontend: http://localhost:5173 (React Vite server)"
echo "   Backend API: http://localhost:$APP_PORT (Laravel application server)"
echo "   Backend Assets: http://localhost:5174 (Laravel Vite server)"
echo ""
echo "🌐 Access your application at: http://localhost:3000"
echo ""
echo "Press Ctrl+C to stop all servers"
echo ""

# Start the development servers
npx concurrently \
  "cd backend && XDEBUG_MODE=off php artisan serve --port=$APP_PORT --host=0.0.0.0" \
  "cd frontend && npm run dev" \
  "cd backend && npm run dev:backend" \
  "npm run proxy" \
  --names "laravel,frontend,backend-vite,proxy" \
  --prefix-colors "red,blue,green,yellow"
