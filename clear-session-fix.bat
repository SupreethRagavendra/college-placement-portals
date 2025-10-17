@echo off
echo ========================================
echo  Fixing 419 Page Expired Error
echo ========================================
echo.

echo [1/5] Clearing configuration cache...
php artisan config:clear
echo.

echo [2/5] Clearing application cache...
php artisan cache:clear
echo.

echo [3/5] Clearing route cache...
php artisan route:clear
echo.

echo [4/5] Clearing view cache...
php artisan view:clear
echo.

echo [5/5] Clearing all optimization caches...
php artisan optimize:clear
echo.

echo ========================================
echo  Session Fix Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Close ALL browser windows
echo 2. Clear browser cache (Ctrl+Shift+Delete)
echo 3. Restart your browser
echo 4. Try logging in again
echo.
pause

