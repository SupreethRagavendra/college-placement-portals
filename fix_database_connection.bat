@echo off
echo Fixing Supabase Database Connection...
echo =====================================
echo.

echo Step 1: Flushing DNS cache...
ipconfig /flushdns
echo DNS cache cleared!
echo.

echo Step 2: Resetting Winsock...
netsh winsock reset
echo Winsock reset complete!
echo.

echo Step 3: Testing Supabase connection...
nslookup db.wkqbukidxmzbgwauncrl.supabase.co
echo.

echo Step 4: Testing with Google DNS...
nslookup db.wkqbukidxmzbgwauncrl.supabase.co 8.8.8.8
echo.

echo Step 5: Testing with Cloudflare DNS...
nslookup db.wkqbukidxmzbgwauncrl.supabase.co 1.1.1.1
echo.

echo If the above tests fail, try:
echo 1. Check your internet connection
echo 2. Disable VPN if using one
echo 3. Check Windows Firewall settings
echo 4. Try changing DNS servers to 8.8.8.8 or 1.1.1.1
echo.

pause
