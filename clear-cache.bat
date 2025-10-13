@echo off
echo Clearing all caches and rebuilding assets...
echo.

echo [1/5] Clearing application cache...
php artisan cache:clear

echo [2/5] Clearing view cache...
php artisan view:clear

echo [3/5] Clearing config cache...
php artisan config:clear

echo [4/5] Clearing route cache...
php artisan route:clear

echo [5/5] Rebuilding assets...
call npm run build

echo.
echo ============================================
echo Cache cleared and assets rebuilt!
echo.
echo IMPORTANT: Clear your browser cache!
echo.
echo Chrome/Edge: Ctrl + Shift + Delete
echo Firefox: Ctrl + Shift + Delete
echo Or do a hard refresh: Ctrl + F5
echo ============================================
echo.
pause

