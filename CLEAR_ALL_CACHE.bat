@echo off
echo ============================================
echo CLEARING ALL CACHES FOR MODE UPDATE FIX
echo ============================================
echo.

echo [1/3] Clearing Laravel cache...
php artisan cache:clear

echo [2/3] Clearing compiled views...
php artisan view:clear

echo [3/3] Clearing config cache...
php artisan config:clear

echo.
echo ============================================
echo ALL CACHES CLEARED!
echo ============================================
echo.
echo NEXT STEPS:
echo 1. Hard refresh browser: Ctrl + Shift + R
echo 2. Open DevTools (F12) and check Console tab
echo 3. Look for: "Chatbot initialized - Mode checking every 3 seconds"
echo.
echo Mode indicator should now update every 3 seconds!
echo ============================================
pause

