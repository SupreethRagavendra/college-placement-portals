@echo off
echo =============================================
echo    EMERGENCY SUPABASE CONNECTION FIX
echo =============================================
echo.

REM Check if running as admin
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Run this as Administrator!
    echo Right-click and select "Run as Administrator"
    pause
    exit /b 1
)

echo Checking Supabase project status...
echo.

REM Try multiple DNS servers
echo Testing with Cloudflare DNS (1.1.1.1)...
nslookup db.wkqbukidxmzbgwauncrl.supabase.co 1.1.1.1
echo.

echo Testing with Quad9 DNS (9.9.9.9)...  
nslookup db.wkqbukidxmzbgwauncrl.supabase.co 9.9.9.9
echo.

REM Common Supabase IPs (AWS regions)
echo.
echo Adding known Supabase IPs to hosts file...
set HOSTS_FILE=C:\Windows\System32\drivers\etc\hosts

REM Remove old entries
powershell -Command "(Get-Content '%HOSTS_FILE%' | Where-Object {$_ -notmatch 'supabase.co'}) | Set-Content '%HOSTS_FILE%'"

REM Add new entries with common Supabase IPs
echo. >> "%HOSTS_FILE%"
echo # Supabase Database Hosts (Emergency Fix) >> "%HOSTS_FILE%"
echo 34.151.192.19 db.wkqbukidxmzbgwauncrl.supabase.co >> "%HOSTS_FILE%"
echo 34.151.225.195 db.wkqbukidxmzbgwauncrl.supabase.co >> "%HOSTS_FILE%"

echo.
echo ✅ Added Supabase IPs to hosts file
echo.

REM Flush DNS
ipconfig /flushdns
echo ✅ DNS cache cleared
echo.

echo Testing connection...
ping -n 1 db.wkqbukidxmzbgwauncrl.supabase.co
echo.

echo =============================================
echo    FIX APPLIED!
echo =============================================
echo.
echo IMPORTANT: If this doesn't work, your Supabase
echo project might be PAUSED or DELETED!
echo.
echo Check: https://app.supabase.com/projects
echo.
pause
