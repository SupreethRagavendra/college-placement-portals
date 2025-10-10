# Run this as Administrator
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   FIXING DNS FOR SUPABASE" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Get all active network adapters
$adapters = Get-NetAdapter | Where-Object {$_.Status -eq "Up"}

foreach ($adapter in $adapters) {
    Write-Host "Setting DNS for: $($adapter.Name)..." -ForegroundColor Yellow
    
    try {
        # Set Google DNS
        Set-DnsClientServerAddress -InterfaceAlias $adapter.Name -ServerAddresses ("8.8.8.8","8.8.4.4")
        Write-Host "✅ DNS set to Google DNS (8.8.8.8, 8.8.4.4)" -ForegroundColor Green
    } catch {
        Write-Host "⚠ Could not set DNS for $($adapter.Name)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "Flushing DNS cache..." -ForegroundColor Yellow
ipconfig /flushdns | Out-Null
Write-Host "✅ DNS cache flushed" -ForegroundColor Green

Write-Host ""
Write-Host "Testing Supabase connection..." -ForegroundColor Yellow
$result = nslookup db.wkqbukidxmzbgwauncrl.supabase.co 2>&1

if ($result -match "Address") {
    Write-Host "✅ Supabase hostname resolves successfully!" -ForegroundColor Green
} else {
    Write-Host "⚠ Still having issues resolving hostname" -ForegroundColor Red
}

Write-Host ""
Write-Host "Clearing Laravel cache..." -ForegroundColor Yellow
cd $PSScriptRoot
php artisan config:clear 2>&1 | Out-Null
Write-Host "✅ Laravel cache cleared" -ForegroundColor Green

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   FIX COMPLETE!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Now try logging in again!" -ForegroundColor Green
Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

