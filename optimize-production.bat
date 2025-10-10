@echo off
REM ================================================================
REM Production Optimization Script for College Placement Portal
REM ================================================================
REM This script optimizes Laravel for production deployment
REM Run this AFTER deploying to production server
REM ================================================================

echo.
echo ============================================================
echo   COLLEGE PLACEMENT PORTAL - PRODUCTION OPTIMIZATION
echo ============================================================
echo.

REM 1. Clear all caches first
echo [1/8] Clearing existing caches...
call php artisan cache:clear
call php artisan config:clear
call php artisan route:clear
call php artisan view:clear
call php artisan event:clear
echo ✓ Caches cleared
echo.

REM 2. Optimize Composer autoloader
echo [2/8] Optimizing Composer autoloader...
call composer install --optimize-autoloader --no-dev
echo ✓ Autoloader optimized
echo.

REM 3. Cache configuration
echo [3/8] Caching configuration...
call php artisan config:cache
echo ✓ Configuration cached
echo.

REM 4. Cache routes
echo [4/8] Caching routes...
call php artisan route:cache
echo ✓ Routes cached
echo.

REM 5. Cache views
echo [5/8] Caching views...
call php artisan view:cache
echo ✓ Views cached
echo.

REM 6. Cache events
echo [6/8] Caching events...
call php artisan event:cache
echo ✓ Events cached
echo.

REM 7. Build frontend assets for production
echo [7/8] Building optimized frontend assets...
call npm run build
echo ✓ Frontend assets built
echo.

REM 8. Run database migrations (if needed)
echo [8/8] Running database migrations...
call php artisan migrate --force
echo ✓ Migrations completed
echo.

echo.
echo ============================================================
echo   OPTIMIZATION COMPLETE!
echo ============================================================
echo.
echo Your application is now optimized for production.
echo.
echo IMPORTANT NOTES:
echo - Make sure APP_ENV=production in .env
echo - Make sure APP_DEBUG=false in .env
echo - Enable OPcache in php.ini for maximum performance
echo - Consider using Redis/Memcached for cache driver
echo - Use queue workers for background jobs
echo.
echo To start queue worker:
echo   php artisan queue:work --daemon
echo.
echo ============================================================

pause

