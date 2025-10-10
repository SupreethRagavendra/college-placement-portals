@echo off
echo ========================================
echo    FIXING SUPABASE DNS ISSUE
echo ========================================
echo.

REM Clear DNS cache
echo Step 1: Clearing DNS cache...
ipconfig /flushdns
echo ✅ DNS cache cleared
echo.

REM Release and renew IP
echo Step 2: Releasing IP configuration...
ipconfig /release >nul 2>&1
echo Step 3: Renewing IP configuration...
ipconfig /renew >nul 2>&1
echo ✅ IP configuration renewed
echo.

REM Reset Winsock
echo Step 4: Resetting network stack...
netsh winsock reset >nul 2>&1
echo ✅ Network stack reset
echo.

REM Set Google DNS as primary (temporary)
echo Step 5: Setting Google DNS temporarily...
netsh interface ip set dns "Wi-Fi" static 8.8.8.8 primary >nul 2>&1
netsh interface ip add dns "Wi-Fi" 8.8.4.4 index=2 >nul 2>&1

REM Try for Ethernet too
netsh interface ip set dns "Ethernet" static 8.8.8.8 primary >nul 2>&1
netsh interface ip add dns "Ethernet" 8.8.4.4 index=2 >nul 2>&1

echo ✅ Google DNS configured
echo.

REM Test connection
echo Step 6: Testing Supabase connection...
nslookup db.wkqbukidxmzbgwauncrl.supabase.co 8.8.8.8
echo.

echo ========================================
echo    FIX COMPLETE!
echo ========================================
echo.
echo Now try running: php artisan serve
echo.
echo If this didn't work, run as Administrator!
echo.
pause
