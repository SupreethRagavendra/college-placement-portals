@echo off
echo Adding Supabase to Windows Hosts File (Temporary Fix)
echo =====================================================
echo.
echo This will add Supabase IP to your hosts file as a temporary workaround
echo.

REM Need to run as Administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: This script must be run as Administrator!
    echo Right-click and select "Run as Administrator"
    pause
    exit /b 1
)

echo Finding Supabase IP address...
for /f "tokens=2" %%a in ('nslookup db.wkqbukidxmzbgwauncrl.supabase.co 8.8.8.8 2^>nul ^| findstr /i "Address" ^| findstr /v "8.8.8.8"') do set SUPABASE_IP=%%a

if "%SUPABASE_IP%"=="" (
    echo Could not resolve Supabase IP. Trying alternative DNS...
    for /f "tokens=2" %%a in ('nslookup db.wkqbukidxmzbgwauncrl.supabase.co 1.1.1.1 2^>nul ^| findstr /i "Address" ^| findstr /v "1.1.1.1"') do set SUPABASE_IP=%%a
)

if "%SUPABASE_IP%"=="" (
    echo ERROR: Cannot resolve Supabase hostname!
    echo Please check your internet connection.
    pause
    exit /b 1
)

echo Found Supabase IP: %SUPABASE_IP%
echo.

set HOSTS_FILE=C:\Windows\System32\drivers\etc\hosts
set SUPABASE_HOST=db.wkqbukidxmzbgwauncrl.supabase.co

REM Check if entry already exists
findstr /c:"%SUPABASE_HOST%" "%HOSTS_FILE%" >nul 2>&1
if %errorlevel% equ 0 (
    echo Entry already exists in hosts file. Updating...
    powershell -Command "(Get-Content '%HOSTS_FILE%') -replace '.*%SUPABASE_HOST%.*', '%SUPABASE_IP% %SUPABASE_HOST%' | Set-Content '%HOSTS_FILE%'"
) else (
    echo Adding new entry to hosts file...
    echo. >> "%HOSTS_FILE%"
    echo # Supabase Database Host >> "%HOSTS_FILE%"
    echo %SUPABASE_IP% %SUPABASE_HOST% >> "%HOSTS_FILE%"
)

echo.
echo ✅ Hosts file updated successfully!
echo.
echo Testing connection...
ping -n 1 %SUPABASE_HOST%
echo.
echo Now try running your Laravel application again.
pause
