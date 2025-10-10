@echo off
echo ========================================
echo   SUPABASE DNS FIX - RUN AS ADMIN
echo ========================================
echo.

REM Check for admin rights
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Not running as Administrator!
    echo.
    echo Please RIGHT-CLICK this file and select "Run as Administrator"
    echo.
    pause
    exit /b 1
)

echo Running as Administrator - Good!
echo.

set HOSTS_FILE=C:\Windows\System32\drivers\etc\hosts
set SUPABASE_HOST=db.wkqbukidxmzbgwauncrl.supabase.co
set SUPABASE_IP=2406:da18:243:740a:6d30:33f9:31ae:d2fa

echo Step 1: Backing up hosts file...
copy /Y "%HOSTS_FILE%" "%HOSTS_FILE%.backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%" >nul 2>&1
echo Done!
echo.

echo Step 2: Checking if entry already exists...
findstr /C:"%SUPABASE_HOST%" "%HOSTS_FILE%" >nul 2>&1
if %errorlevel% equ 0 (
    echo Entry already exists - removing old entry...
    powershell -Command "(Get-Content '%HOSTS_FILE%') | Where-Object { $_ -notmatch '%SUPABASE_HOST%' } | Set-Content '%HOSTS_FILE%'"
)
echo.

echo Step 3: Adding Supabase to hosts file...
echo. >> "%HOSTS_FILE%"
echo # Supabase Database - Added by fix script >> "%HOSTS_FILE%"
echo %SUPABASE_IP%    %SUPABASE_HOST% >> "%HOSTS_FILE%"
echo Done!
echo.

echo Step 4: Flushing DNS cache...
ipconfig /flushdns >nul
echo Done!
echo.

echo Step 5: Testing connection...
cd /d "%~dp0"
php test-db-connection.php
echo.

echo ========================================
echo   FIX COMPLETE!
echo ========================================
echo.
echo If the test shows SUCCESS, you're good to go!
echo.
echo Now:
echo   1. Stop your server (Ctrl+C)
echo   2. Run: php artisan serve
echo   3. Try logging in!
echo.
pause

