@echo off
echo ========================================
echo Fixing Profile Update Issue
echo ========================================
echo.

echo [1] Clearing all caches...
php artisan optimize:clear

echo.
echo [2] Creating session fix configuration...
echo SESSION_DRIVER=file > session_fix.txt
echo.

echo [3] Setting session driver to file temporarily...
php artisan tinker --execute="Config::set('session.driver', 'file');"

echo.
echo [4] Clearing sessions...
del /Q storage\framework\sessions\* 2>nul

echo.
echo ========================================
echo Fix Applied! 
echo ========================================
echo.
echo IMPORTANT: To permanently fix this:
echo 1. Open your .env file
echo 2. Change: SESSION_DRIVER=database
echo 3. To:     SESSION_DRIVER=file
echo.
echo Or run: php artisan session:table
echo Then:   php artisan migrate
echo.
pause
