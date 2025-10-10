# Complete DNS Fix for Supabase Connection
# Run as Administrator!

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   COMPLETE DNS FIX FOR SUPABASE" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check for admin rights
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "❌ ERROR: This script must be run as Administrator!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please right-click and select 'Run as Administrator'" -ForegroundColor Yellow
    Write-Host ""
    pause
    exit 1
}

Write-Host "Running as Administrator ✅" -ForegroundColor Green
Write-Host ""

# Step 1: Flush DNS Cache
Write-Host "Step 1: Flushing DNS cache..." -ForegroundColor Yellow
ipconfig /flushdns | Out-Null
Write-Host "✅ DNS cache flushed" -ForegroundColor Green
Write-Host ""

# Step 2: Reset network stack
Write-Host "Step 2: Resetting network stack..." -ForegroundColor Yellow
netsh winsock reset | Out-Null
Write-Host "✅ Network stack reset" -ForegroundColor Green
Write-Host ""

# Step 3: Get active network adapters and set Google DNS
Write-Host "Step 3: Setting Google DNS on all active adapters..." -ForegroundColor Yellow
$adapters = Get-NetAdapter | Where-Object {$_.Status -eq "Up"}

foreach ($adapter in $adapters) {
    try {
        Set-DnsClientServerAddress -InterfaceAlias $adapter.Name -ServerAddresses ("8.8.8.8","8.8.4.4") -ErrorAction Stop
        Write-Host "  ✅ Set DNS for: $($adapter.Name)" -ForegroundColor Green
    } catch {
        Write-Host "  ⚠ Could not set DNS for: $($adapter.Name)" -ForegroundColor Yellow
    }
}
Write-Host ""

# Step 4: Flush DNS again after changing servers
Write-Host "Step 4: Flushing DNS cache again..." -ForegroundColor Yellow
ipconfig /flushdns | Out-Null
Write-Host "✅ DNS cache flushed" -ForegroundColor Green
Write-Host ""

# Step 5: Test DNS resolution with nslookup
Write-Host "Step 5: Testing DNS resolution..." -ForegroundColor Yellow
$nslookupResult = nslookup db.wkqbukidxmzbgwauncrl.supabase.co 8.8.8.8 2>&1 | Out-String
if ($nslookupResult -match "Address") {
    Write-Host "✅ nslookup can resolve hostname" -ForegroundColor Green
} else {
    Write-Host "❌ nslookup failed" -ForegroundColor Red
}
Write-Host ""

# Step 6: Wait for DNS to propagate
Write-Host "Step 6: Waiting for DNS changes to propagate..." -ForegroundColor Yellow
Start-Sleep -Seconds 3
Write-Host "✅ Wait complete" -ForegroundColor Green
Write-Host ""

# Step 7: Test from PHP
Write-Host "Step 7: Testing from PHP..." -ForegroundColor Yellow
Set-Location $PSScriptRoot
$phpTest = php test-db-connection.php 2>&1 | Out-String
Write-Host $phpTest
Write-Host ""

# Step 8: Clear Laravel caches
Write-Host "Step 8: Clearing Laravel caches..." -ForegroundColor Yellow
php artisan config:clear 2>&1 | Out-Null
php artisan cache:clear 2>&1 | Out-Null
php artisan view:clear 2>&1 | Out-Null
php artisan route:clear 2>&1 | Out-Null
Write-Host "✅ Laravel caches cleared" -ForegroundColor Green
Write-Host ""

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   DNS FIX COMPLETE!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "If the PHP test still shows DNS errors, you may need to:" -ForegroundColor Yellow
Write-Host "  1. Restart your computer (for network stack reset to take effect)" -ForegroundColor White
Write-Host "  2. Check if you're using a VPN (disable it temporarily)" -ForegroundColor White
Write-Host "  3. Check Windows Firewall settings" -ForegroundColor White
Write-Host "  4. Contact your network administrator" -ForegroundColor White
Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

