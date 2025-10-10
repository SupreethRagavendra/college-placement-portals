@echo off
echo ========================================
echo   FIXING SESSION/CACHE LOGIN ISSUE
echo ========================================
echo.
echo This will fix the login issue that works in incognito mode
echo.

cd /d "%~dp0"

echo Step 1: Switching to file-based sessions (temporary)...
echo.

REM Backup current .env
if exist .env (
    copy /Y .env .env.backup_session >nul 2>&1
    echo ✅ Backed up .env
)

REM Update SESSION_DRIVER in .env
powershell -Command "(Get-Content .env) -replace 'SESSION_DRIVER=database', 'SESSION_DRIVER=file' | Set-Content .env"
powershell -Command "if ((Get-Content .env | Select-String 'SESSION_DRIVER').Count -eq 0) { Add-Content .env 'SESSION_DRIVER=file' }"
echo ✅ Switched to file-based sessions
echo.

echo Step 2: Clearing all caches...
php artisan config:clear
php artisan view:clear
php artisan route:clear
echo ✅ Laravel cache cleared
echo.

echo Step 3: Clearing session files...
if exist "storage\framework\sessions\*" (
    del /Q storage\framework\sessions\* 2>nul
    echo ✅ Session files cleared
) else (
    echo ⚠ No session files to clear
)
echo.

echo Step 4: Clearing browser instructions...
echo.
echo ========================================
echo   NOW CLEAR YOUR BROWSER:
echo ========================================
echo.
echo 1. Press Ctrl + Shift + Delete
echo 2. Select "Cookies and other site data"
echo 3. Select "Cached images and files"
echo 4. Click "Clear data"
echo.
echo OR simply restart your browser
echo.
echo ========================================
echo   FIX COMPLETE!
echo ========================================
echo.
echo Now try logging in again!
echo.
pause

