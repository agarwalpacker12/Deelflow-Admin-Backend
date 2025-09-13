# Railway Deployment Guide

This guide explains how to deploy the Dealflow Laravel backend to Railway.

## Prerequisites

1. Railway account (https://railway.app)
2. GitHub repository connected to Railway
3. PostgreSQL database addon

## Deployment Steps

### 1. Connect Repository to Railway

1. Go to Railway dashboard
2. Click "New Project"
3. Select "Deploy from GitHub repo"
4. Choose your repository
5. Set root directory to `backend`

### 2. Add PostgreSQL Database

1. In your Railway project, click "New"
2. Select "Database" → "PostgreSQL"
3. Railway will automatically provision a PostgreSQL database

### 3. Configure Environment Variables

In Railway dashboard, go to your service settings and add these environment variables:

```env
APP_NAME="Dealflow API"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-railway-app.railway.app

LOG_LEVEL=error

# Database (Railway will auto-populate this)
DB_CONNECTION=pgsql
DB_URL=postgresql://${{Postgres.PGUSER}}:${{Postgres.PGPASSWORD}}@${{Postgres.PGHOST}}:${{Postgres.PGPORT}}/${{Postgres.PGDATABASE}}

# CORS Configuration
CORS_ALLOWED_ORIGINS="https://your-frontend-app.onrender.com"

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS="your-frontend-app.onrender.com"

# Disable mock data in production
MOCK_DATA_ENABLED=false
```

### 4. Generate Application Key

Railway will automatically run `php artisan key:generate` during deployment.

### 5. Database Migrations

Railway will automatically run migrations during deployment via the `post-install-cmd` script in `composer.json`. The script includes:

```json
"post-install-cmd": [
    "@php artisan migrate --force",
    "@php artisan optimize:clear",
    "@php artisan config:cache",
    "@php artisan route:cache"
]
```

The `--force` flag is used to run migrations in production without confirmation prompts.

## Important Notes

### Database Connection
- Railway automatically provides PostgreSQL connection variables
- Use Railway's variable references: `${{Postgres.PGHOST}}`, etc.

### CORS Configuration
- Update `CORS_ALLOWED_ORIGINS` with your actual Render.com frontend URL
- Update `SANCTUM_STATEFUL_DOMAINS` with your frontend domain

### Environment Variables to Update
1. `APP_URL` - Your Railway app URL
2. `CORS_ALLOWED_ORIGINS` - Your Render.com frontend URL
3. `SANCTUM_STATEFUL_DOMAINS` - Your frontend domain

### Automatic Deployment
- Railway will automatically deploy when you push to your `develop` branch
- No CI/CD configuration files needed

## Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Ensure PostgreSQL addon is added to your project
   - Verify database environment variables are correctly set

2. **CORS Errors**
   - Update `CORS_ALLOWED_ORIGINS` with your frontend URL
   - Ensure frontend is making requests to correct backend URL

3. **Key Generation**
   - If app key is missing, Railway should auto-generate it
   - You can manually set `APP_KEY` in environment variables

4. **Cache Table Missing Error**
   ```
   SQLSTATE[42P01]: Undefined table: 7 ERROR: relation "cache" does not exist
   ```
   - This occurs when `optimize:clear` runs before migrations
   - Fixed by adding `@php artisan migrate --force` before `optimize:clear` in `post-install-cmd`
   - If you encounter this, redeploy after updating the `composer.json` file

5. **Views Directory Missing Error**
   ```
   The "/app/resources/views" directory does not exist.
   ```
   - This occurs when `view:cache` runs on an API-only application without Blade views
   - Fixed by removing `@php artisan view:cache` from `post-install-cmd` since it's not needed for API applications
   - API applications don't use Blade templates, so view caching is unnecessary

### Logs
- Check Railway logs in the dashboard for deployment issues
- Use `LOG_LEVEL=debug` temporarily for more detailed logs

## Frontend Integration

Update your frontend's API configuration to point to your Railway backend URL:

```javascript
// In your frontend environment variables
REACT_APP_API_URL=https://your-railway-app.railway.app/api
```

## Health Check

Railway provides a health check endpoint at `/up` (configured in `bootstrap/app.php`).
