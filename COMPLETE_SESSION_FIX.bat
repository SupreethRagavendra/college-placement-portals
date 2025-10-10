@echo off
setlocal enabledelayedexpansion

echo ========================================
echo   COMPLETE SESSION/LOGIN FIX
echo ========================================
echo.
echo Issue: Works in incognito but not normal browser
echo Root Cause: Corrupted session/cache data
echo.

cd /d "%~dp0"

echo Step 1: Checking for .env file...
if not exist ".env" (
    echo ⚠ WARNING: .env file not found!
    echo.
    echo Creating .env from setup...
    if exist "setup-env.php" (
        php setup-env.php
        echo ✅ Created .env file
    ) else (
        echo ❌ Cannot find setup-env.php
        echo.
        echo Please create .env file manually or copy from .env.example
        pause
        exit /b 1
    )
) else (
    echo ✅ .env file exists
)
echo.

echo Step 2: Clearing session storage directory...
if exist "storage\framework\sessions" (
    del /Q storage\framework\sessions\* 2>nul
    echo ✅ Session files cleared
) else (
    echo ⚠ Session directory not found
)
echo.

echo Step 3: Clearing Laravel cache files...
if exist "bootstrap\cache\config.php" (
    del /Q bootstrap\cache\config.php 2>nul
    echo ✅ Config cache deleted
)
if exist "bootstrap\cache\routes-v7.php" (
    del /Q bootstrap\cache\routes-v7.php 2>nul
    echo ✅ Routes cache deleted
)
if exist "bootstrap\cache\services.php" (
    del /Q bootstrap\cache\services.php 2>nul
    echo ✅ Services cache deleted
)
echo.

echo Step 4: Switching to file-based sessions (safer)...
if exist ".env" (
    REM Backup .env
    copy /Y .env .env.backup_before_session_fix >nul 2>&1
    
    REM Replace or add SESSION_DRIVER
    powershell -Command "$content = Get-Content .env -Raw; if ($content -match 'SESSION_DRIVER=') { $content -replace 'SESSION_DRIVER=.*', 'SESSION_DRIVER=file' | Set-Content .env } else { Add-Content .env \"`nSESSION_DRIVER=file\" }"
    echo ✅ Switched to file-based sessions
) else (
    echo ⚠ Cannot update .env (file not found)
)
echo.

echo Step 5: Running Laravel cache clear commands...
echo.
php artisan config:clear 2>nul
php artisan cache:clear 2>nul
php artisan view:clear 2>nul
php artisan route:clear 2>nul
echo ✅ All Laravel caches cleared
echo.

echo ========================================
echo   BROWSER CLEANUP REQUIRED
echo ========================================
echo.
echo Now you MUST clear your browser:
echo.
echo Option 1: Clear Browser Data
echo   1. Press Ctrl + Shift + Delete
echo   2. Select "Cookies and other site data"
echo   3. Select "Cached images and files"
echo   4. Time range: "All time"
echo   5. Click "Clear data"
echo.
echo Option 2: Hard Refresh (Simpler)
echo   1. Go to your login page
echo   2. Press Ctrl + Shift + R
echo   3. Or press Ctrl + F5
echo.
echo Option 3: Restart Browser
echo   - Close ALL browser windows
echo   - Wait 5 seconds
echo   - Open browser again
echo.
echo ========================================
echo   FIX COMPLETE!
echo ========================================
echo.
echo After clearing browser cache, try logging in!
echo.
pause


