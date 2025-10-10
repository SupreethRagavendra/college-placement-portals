@echo off
echo ========================================
echo   FINAL DNS FIX - COMPLETE SOLUTION
echo ========================================
echo.
echo This fixes the "could not translate host name" error
echo.

cd /d "%~dp0"

echo Step 1: Backing up .env...
copy /Y .env .env.backup_final_fix >nul 2>&1
echo Backed up .env
echo.

echo Step 2: Updating .env with correct cache settings...
echo.

REM The key was to set CACHE_STORE=file (not just CACHE_DRIVER)
powershell -Command "$env = Get-Content .env -Raw; if ($env -notmatch 'CACHE_STORE=') { Add-Content .env 'CACHE_STORE=file' } else { $env = $env -replace 'CACHE_STORE=.*', 'CACHE_STORE=file'; $env | Set-Content .env }"

powershell -Command "$env = Get-Content .env -Raw; if ($env -notmatch 'CACHE_DRIVER=') { Add-Content .env 'CACHE_DRIVER=file' } else { $env = $env -replace 'CACHE_DRIVER=.*', 'CACHE_DRIVER=file'; $env | Set-Content .env }"

powershell -Command "$env = Get-Content .env -Raw; if ($env -notmatch 'SESSION_DRIVER=') { Add-Content .env 'SESSION_DRIVER=file' } else { $env = $env -replace 'SESSION_DRIVER=.*', 'SESSION_DRIVER=file'; $env | Set-Content .env }"

echo Updated .env file with:
echo   - CACHE_STORE=file
echo   - CACHE_DRIVER=file
echo   - SESSION_DRIVER=file
echo.

echo Step 3: Clearing all cache files...
del /Q storage\framework\cache\data\* 2>nul
del /Q storage\framework\sessions\* 2>nul
del /Q bootstrap\cache\*.php 2>nul
echo Cleared cache files
echo.

echo Step 4: Testing cache configuration...
php artisan tinker --execute="echo 'Cache driver: ' . config('cache.default');"
echo.

echo Step 5: Clearing application cache...
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
echo All caches cleared
echo.

echo ========================================
echo   SERVER FIX COMPLETE!
echo ========================================
echo.
echo Now clear your browser:
echo   1. Press Ctrl + Shift + R on login page
echo   2. OR Press Ctrl + Shift + Delete
echo.
echo Then try logging in!
echo.
echo If server is running, restart it:
echo   - Stop with Ctrl + C
echo   - Run: php artisan serve
echo.
pause

