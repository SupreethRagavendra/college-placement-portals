@echo off
echo ========================================
echo   FIXING TENANT ERROR - REMOVING HOSTS
echo ========================================
echo.

REM Check for admin
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Must run as Administrator!
    echo Right-click this file and "Run as administrator"
    pause
    exit /b 1
)

set HOSTS_FILE=C:\Windows\System32\drivers\etc\hosts

echo Step 1: Backing up hosts file...
copy /Y "%HOSTS_FILE%" "%HOSTS_FILE%.backup_remove" >nul 2>&1
echo Done!
echo.

echo Step 2: Removing Supabase IP mappings...
powershell -Command "(Get-Content '%HOSTS_FILE%') | Where-Object { $_ -notmatch 'supabase' -and $_ -notmatch '3.108.251.216' -and $_ -notmatch '2406:da18' } | Set-Content '%HOSTS_FILE%'"
echo Done!
echo.

echo Step 3: Flushing DNS...
ipconfig /flushdns >nul
ipconfig /registerdns >nul
echo Done!
echo.

echo Step 4: Waiting for DNS to stabilize...
timeout /t 3 /nobreak >nul
echo Done!
echo.

echo Step 5: Testing DNS resolution...
echo.
nslookup db.wkqbukidxmzbgwauncrl.supabase.co 8.8.8.8
echo.

echo Step 6: Testing from PHP...
cd /d "%~dp0"
php -r "echo 'PHP resolves to: ' . gethostbyname('db.wkqbukidxmzbgwauncrl.supabase.co') . PHP_EOL;"
echo.

echo Step 7: Clearing Laravel cache...
php artisan config:clear
echo.

echo Step 8: Testing database connection...
php artisan tinker --execute="try { DB::connection()->getPdo(); echo PHP_EOL . '✅✅✅ SUCCESS! DATABASE CONNECTED!' . PHP_EOL; $users = DB::table('users')->count(); echo '✅ Users table has ' . $users . ' users' . PHP_EOL; } catch (Exception $e) { echo PHP_EOL . '❌ Error: ' . $e->getMessage() . PHP_EOL; }"
echo.

echo ========================================
echo   FIX COMPLETE!
echo ========================================
echo.
echo If it worked, you can now:
echo   1. php artisan serve
echo   2. Try logging in!
echo.
echo If still failing, RESTART your computer
echo.
pause

