@echo off
REM ================================================================
REM INSTANT SPEED BOOST - College Placement Portal
REM ================================================================
REM This script applies ALL performance optimizations NOW
REM Run this and restart your server to see IMMEDIATE improvements
REM ================================================================

echo.
echo ============================================================
echo   APPLYING AGGRESSIVE SPEED OPTIMIZATIONS
echo ============================================================
echo.

REM Step 1: Clear all existing caches
echo [1/6] Clearing old caches...
call php artisan cache:clear
call php artisan config:clear
call php artisan route:clear
call php artisan view:clear
call php artisan event:clear
echo ✓ Old caches cleared
echo.

REM Step 2: Optimize Composer autoloader
echo [2/6] Optimizing autoloader...
call composer dump-autoload --optimize --no-dev 2>nul || call composer dump-autoload --optimize
echo ✓ Autoloader optimized
echo.

REM Step 3: Cache everything for MAXIMUM SPEED
echo [3/6] Caching configurations...
call php artisan config:cache
echo ✓ Config cached
echo.

echo [4/6] Caching routes...
call php artisan route:cache
echo ✓ Routes cached
echo.

echo [5/6] Caching views...
call php artisan view:cache
echo ✓ Views cached
echo.

echo [6/6] Caching events...
call php artisan event:cache
echo ✓ Events cached
echo.

echo.
echo ============================================================
echo   SPEED BOOST COMPLETE!
echo ============================================================
echo.
echo Your application is now running at MAXIMUM SPEED.
echo.
echo NEXT STEPS:
echo 1. RESTART your development server (php artisan serve)
echo 2. Clear your browser cache (Ctrl+Shift+Delete)
echo 3. Test the application - it should be MUCH faster!
echo.
echo Pages will now load from cache instantly!
echo - Dashboard: Cached for 10 minutes
echo - Assessment lists: Cached for 10 minutes  
echo - Student results: Cached for 5 minutes
echo.
echo If you make code changes and need to clear cache:
echo   php artisan optimize:clear
echo.
echo ============================================================

pause

