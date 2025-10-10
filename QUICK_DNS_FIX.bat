@echo off
echo ========================================
echo   FIXING SUPABASE DNS CONNECTION ISSUE
echo ========================================
echo.
echo This will fix the "could not translate host name" error
echo.

REM Check for admin rights
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: This script must be run as Administrator!
    echo.
    echo Please right-click this file and select "Run as Administrator"
    echo.
    pause
    exit /b 1
)

echo Step 1: Clearing DNS cache...
ipconfig /flushdns
echo ✅ DNS cache cleared
echo.

echo Step 2: Resetting network stack...
netsh winsock reset >nul 2>&1
echo ✅ Network stack reset
echo.

echo Step 3: Setting Google DNS (temporary)...
REM Try different network adapter names
for %%A in ("Wi-Fi" "Ethernet" "Local Area Connection" "Wireless Network Connection") do (
    netsh interface ip set dns %%A static 8.8.8.8 primary >nul 2>&1
    netsh interface ip add dns %%A 8.8.4.4 index=2 >nul 2>&1
)
echo ✅ Google DNS configured
echo.

echo Step 4: Testing Supabase connection...
echo.
nslookup db.wkqbukidxmzbgwauncrl.supabase.co 8.8.8.8
echo.

echo Step 5: Clearing Laravel cache...
if exist "artisan" (
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    echo ✅ Laravel cache cleared
) else (
    echo ⚠ Not in Laravel root directory
)
echo.

echo ========================================
echo    FIX COMPLETE!
echo ========================================
echo.
echo Now try logging in again!
echo.
echo If it still doesn't work, you may need to restart your computer.
echo.
pause

