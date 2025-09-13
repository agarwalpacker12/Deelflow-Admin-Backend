# Real Estate Wholesaling Platform

This is a monorepo for the Real Estate Wholesaling Platform, encompassing both the frontend (React.js) and backend (Laravel) applications with a unified development server architecture.

## Project Overview

The platform aims to streamline the real estate wholesaling process through AI-assisted lead management, a marketplace for property listings, transaction automation, and predictive analytics for deal assessment.

Refer to `docs/dealflow_mvp_srs.md` for detailed Software Requirements Specifications.

## Repository Structure

-   `/backend`: Contains the Laravel backend application with Vite integration
-   `/frontend`: Contains the React.js frontend application
-   `/docs`: Contains project documentation, including the SRS
-   `/.github`: Contains GitHub-specific files like workflows and issue templates

## Quick Start

### Option 1: One-Command Startup (Recommended)

```bash
./start-dev.sh
```

This script will:
- Install dependencies if needed
- Start both frontend and backend servers
- Set up the unified development environment

### Option 2: Manual Setup

```bash
# Install dependencies
cd frontend && npm install && cd ..
cd backend && npm install && cd ..

# Start development servers
cd backend && npm run dev:full
```

## Development Architecture

This project uses a **unified server architecture** where:

- **Backend Vite Server** (Port 5174): Main entry point
  - Handles Laravel API routes (`/api/*`)
  - Proxies all other requests to the frontend
  - Compiles Laravel assets (CSS/JS)

- **Frontend Vite Server** (Port 5173): React development server
  - Serves the React application
  - Provides hot module replacement

### Access Points

- **Main Application**: http://localhost:5174 (Recommended)
- **Direct Frontend**: http://localhost:5173 (For frontend-only development)

## Key Features

- **Single Entry Point**: Access your entire application through one URL
- **Hot Reloading**: Both frontend and backend support live reloading
- **API Integration**: Seamless communication between React and Laravel
- **Production-Like Setup**: Mimics production deployment architecture

## Documentation

- [Detailed Setup Instructions](SETUP_INSTRUCTIONS.md)
- [API Documentation](docs/api-documentation.md)
- [Software Requirements](docs/dealflow_mvp_srs.md)

## System Specifications

### Runtime Requirements

#### Backend (Laravel API)
- **PHP**: ^8.2 (minimum)
- **Composer**: Latest stable version
- **Node.js**: 18.x or higher (for Vite build tools)
- **NPM**: 8.x or higher

#### Frontend (React SPA)
- **Node.js**: 18.x or higher
- **NPM**: 8.x or higher

### Database Support
- **Primary**: PostgreSQL (production recommended)
- **Development**: SQLite (default for local development)
- **Supported**: MySQL 8.0+, MariaDB 10.3+, SQL Server 2017+
- **Cache/Session**: Redis (optional, database fallback available)

### Server Requirements

#### Development Environment
- **Memory**: 4GB RAM minimum, 8GB recommended
- **Storage**: 2GB free space minimum
- **Ports**: 
  - 5173 (Frontend Vite server)
  - 5174 (Backend Vite server) 
  - 8000 (Laravel application server)
  - 5432 (PostgreSQL, if local)
  - 6379 (Redis, if used)

#### Production Environment
- **Memory**: 8GB RAM minimum, 16GB+ recommended
- **Storage**: 10GB+ free space
- **Web Server**: Nginx or Apache with PHP-FPM
- **SSL**: Required for production deployment
- **Process Manager**: PM2, Supervisor, or similar

### Technology Stack

#### Backend Dependencies
- **Framework**: Laravel ^12.0
- **Authentication**: Laravel Sanctum ^4.1
- **Testing**: PHPUnit ^11.5.3
- **Code Quality**: Laravel Pint ^1.13
- **Development**: Laravel Sail ^1.41, Laravel Pail ^1.2.2

#### Frontend Dependencies
- **Core**: React ^18.2.0, React DOM ^18.2.0
- **Build Tool**: Vite ^5.0.1
- **Styling**: TailwindCSS ^3.3.5, Headless UI ^1.7.17
- **State Management**: Redux Toolkit ^1.9.7, React Redux ^8.1.3
- **Routing**: React Router DOM ^6.17.0
- **HTTP Client**: Axios ^1.10.0
- **Forms**: React Hook Form ^7.60.0, Yup ^1.6.1
- **UI Components**: Heroicons ^2.0.18, Lucide React ^0.292.0
- **Charts**: Chart.js ^4.4.0, React Chart.js 2 ^5.2.0, Recharts ^2.8.0
- **Animation**: Framer Motion ^10.16.4
- **Web3**: Ethers ^6.8.1, Web3 ^4.2.2, Web3-React ^8.2.3
- **Real-time**: Socket.io Client ^4.7.2
- **Data Fetching**: TanStack React Query ^5.81.5
- **Utilities**: Date-fns ^2.30.0, React Hot Toast ^2.4.1

#### Development Tools
- **Build System**: Vite ^6.2.4 (Backend), Vite ^5.0.1 (Frontend)
- **CSS Framework**: TailwindCSS ^4.0.0 (Backend), ^3.3.5 (Frontend)
- **Process Management**: Concurrently ^9.0.1
- **Testing**: Jest, React Testing Library ^16.3.0
- **Type Checking**: TypeScript ^4.9.5

### Browser Compatibility

#### Production Support
- Chrome/Edge: Last 2 versions
- Firefox: Last 2 versions  
- Safari: Last 2 versions
- Mobile browsers: iOS Safari 14+, Chrome Mobile 90+

#### Development Support
- Chrome: Latest version
- Firefox: Latest version
- Safari: Latest version

### Environment Configuration

#### Required Environment Variables
```bash
# Application
APP_NAME="Dealflow API"
APP_ENV=production|local
APP_KEY=base64:generated_key
APP_DEBUG=true|false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=pgsql|mysql|sqlite
DB_URL=postgresql://user:pass@host:port/db
# OR individual DB settings
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=dealflow
DB_USERNAME=username
DB_PASSWORD=password

# Cache & Sessions
CACHE_STORE=database|redis
SESSION_DRIVER=database|redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# CORS & Sanctum (for SPA)
CORS_ALLOWED_ORIGINS=https://frontend-domain.com
SANCTUM_STATEFUL_DOMAINS=frontend-domain.com
```

### Performance Specifications

#### Recommended Hardware (Production)
- **CPU**: 4+ cores, 2.4GHz+
- **RAM**: 16GB+ 
- **Storage**: SSD with 50GB+ available
- **Network**: 1Gbps+ connection
- **Load Balancer**: Nginx/Apache reverse proxy

#### Scalability Features
- **Database**: Connection pooling, query optimization
- **Caching**: Redis for sessions and application cache
- **Assets**: CDN-ready static asset compilation
- **API**: RESTful design with efficient pagination
- **Real-time**: WebSocket support via Socket.io

### Security Requirements
- **HTTPS**: Required for production
- **CORS**: Configurable cross-origin resource sharing
- **Authentication**: Token-based via Laravel Sanctum
- **Database**: Prepared statements, ORM protection
- **Environment**: Secure environment variable management
## Technology Stack

- **Frontend**: React.js, Vite, TailwindCSS, Redux Toolkit
- **Backend**: Laravel, Vite, PHP
- **Development**: Unified Vite proxy architecture

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test using `./start-dev.sh`
5. Submit a pull request

## License

(License information to be added)
