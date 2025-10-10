@echo off
echo ========================================
echo   FIXING CACHE AND SESSION DNS ERROR
echo ========================================
echo.
echo Error: "could not translate host name"
echo Fix: Switch to file-based cache and sessions
echo.

cd /d "%~dp0"

echo Step 1: Checking for .env file...
if not exist ".env" (
    echo Creating .env file...
    if exist "setup-env.php" (
        php setup-env.php
        echo Created .env using setup-env.php
    ) else if exist ".env.example" (
        copy .env.example .env
        echo Created .env from .env.example
    ) else (
        echo WARNING: No .env or .env.example found!
        echo Creating minimal .env...
        (
            echo APP_NAME=Laravel
            echo APP_ENV=local
            echo APP_DEBUG=true
            echo APP_URL=http://localhost:8000
            echo.
            echo # Use file-based storage to avoid DNS issues
            echo CACHE_DRIVER=file
            echo SESSION_DRIVER=file
            echo QUEUE_CONNECTION=sync
            echo.
            echo # Database - configure these properly
            echo DB_CONNECTION=pgsql
            echo DB_HOST=db.wkqbukidxmzbgwauncrl.supabase.co
            echo DB_PORT=5432
            echo DB_DATABASE=postgres
            echo DB_USERNAME=postgres
            echo DB_PASSWORD=your-password-here
        ) > .env
        php artisan key:generate --force
        echo Created minimal .env file
    )
) else (
    echo .env file exists
)
echo.

echo Step 2: Updating .env to use file-based cache and sessions...

REM Backup .env
copy /Y .env .env.backup_dns_fix >nul 2>&1

REM Update or add CACHE_DRIVER
powershell -Command "$env = Get-Content .env -Raw; if ($env -match 'CACHE_DRIVER=') { $env -replace 'CACHE_DRIVER=.*', 'CACHE_DRIVER=file' | Set-Content .env } else { Add-Content .env \"`nCACHE_DRIVER=file\" }"

REM Update or add SESSION_DRIVER
powershell -Command "$env = Get-Content .env -Raw; if ($env -match 'SESSION_DRIVER=') { $env -replace 'SESSION_DRIVER=.*', 'SESSION_DRIVER=file' | Set-Content .env } else { Add-Content .env \"`nSESSION_DRIVER=file\" }"

echo Updated .env file
echo.

echo Step 3: Clearing all cache and session files...

REM Clear cache directory
if exist "storage\framework\cache\data" (
    del /S /Q storage\framework\cache\data\* 2>nul
    echo Cleared cache data
)

REM Clear session directory
if exist "storage\framework\sessions" (
    del /Q storage\framework\sessions\* 2>nul
    echo Cleared session files
)

REM Clear bootstrap cache
if exist "bootstrap\cache" (
    del /Q bootstrap\cache\*.php 2>nul
    echo Cleared bootstrap cache
)
echo.

echo Step 4: Running artisan cache clear commands...
php artisan config:clear
php artisan view:clear
php artisan route:clear
echo Laravel caches cleared
echo.

echo ========================================
echo   SERVER SIDE FIX COMPLETE!
echo ========================================
echo.
echo Now clear your browser:
echo   1. Press Ctrl + Shift + R on login page
echo   2. OR Press Ctrl + Shift + Delete to clear all data
echo.
echo Then try logging in again!
echo.
pause

