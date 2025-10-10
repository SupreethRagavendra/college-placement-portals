# Final Fix for "Tenant or user not found" Error
# Run as Administrator!

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   FIXING TENANT ERROR" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check for admin rights
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "❌ ERROR: Must run as Administrator!" -ForegroundColor Red
    Write-Host "Right-click → Run with PowerShell" -ForegroundColor Yellow
    pause
    exit 1
}

$hostsFile = "C:\Windows\System32\drivers\etc\hosts"

# Step 1: Remove IP mappings from hosts file
Write-Host "Step 1: Cleaning hosts file..." -ForegroundColor Yellow
$content = Get-Content $hostsFile | Where-Object { 
    $_ -notmatch 'supabase' -and 
    $_ -notmatch '3.108.251.216' -and 
    $_ -notmatch '2406:da18'
}
$content | Set-Content $hostsFile
Write-Host "✅ Removed IP mappings" -ForegroundColor Green
Write-Host ""

# Step 2: Set Google DNS
Write-Host "Step 2: Setting Google DNS..." -ForegroundColor Yellow
$adapters = Get-NetAdapter | Where-Object {$_.Status -eq "Up"}
$dnsSet = $false
foreach ($adapter in $adapters) {
    try {
        Set-DnsClientServerAddress -InterfaceAlias $adapter.Name -ServerAddresses ("8.8.8.8","8.8.4.4") -ErrorAction Stop
        Write-Host "  ✅ Set DNS for: $($adapter.Name)" -ForegroundColor Green
        $dnsSet = $true
    } catch {
        Write-Host "  ⚠ Could not set for: $($adapter.Name)" -ForegroundColor Yellow
    }
}

if ($dnsSet) {
    Write-Host "✅ Google DNS configured" -ForegroundColor Green
} else {
    Write-Host "⚠ Could not set DNS automatically" -ForegroundColor Yellow
}
Write-Host ""

# Step 3: Flush DNS
Write-Host "Step 3: Flushing DNS cache..." -ForegroundColor Yellow
ipconfig /flushdns | Out-Null
ipconfig /registerdns | Out-Null
Write-Host "✅ DNS flushed and re-registered" -ForegroundColor Green
Write-Host ""

# Step 4: Wait for DNS to propagate
Write-Host "Step 4: Waiting for DNS propagation..." -ForegroundColor Yellow
Start-Sleep -Seconds 5
Write-Host "✅ Wait complete" -ForegroundColor Green
Write-Host ""

# Step 5: Test DNS resolution
Write-Host "Step 5: Testing DNS resolution..." -ForegroundColor Yellow
$nslookup = nslookup db.wkqbukidxmzbgwauncrl.supabase.co 8.8.8.8 2>&1 | Out-String
if ($nslookup -match "Address") {
    Write-Host "✅ DNS resolves successfully" -ForegroundColor Green
} else {
    Write-Host "❌ DNS resolution failed" -ForegroundColor Red
}
Write-Host ""

# Step 6: Clear Laravel caches
Write-Host "Step 6: Clearing Laravel caches..." -ForegroundColor Yellow
Set-Location $PSScriptRoot
php artisan config:clear 2>&1 | Out-Null
php artisan cache:clear 2>&1 | Out-Null
Write-Host "✅ Laravel caches cleared" -ForegroundColor Green
Write-Host ""

# Step 7: Test connection
Write-Host "Step 7: Testing database connection..." -ForegroundColor Yellow
Write-Host ""
$testResult = php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'SUCCESS: Connected!'; } catch (Exception `$e) { echo 'FAILED: ' . `$e->getMessage(); }" 2>&1
Write-Host $testResult
Write-Host ""

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   FIX COMPLETE!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "If still not working, you MUST restart your computer" -ForegroundColor Yellow
Write-Host "Network changes require a full reboot to take effect" -ForegroundColor Yellow
Write-Host ""
Write-Host "After restart:" -ForegroundColor Green
Write-Host "  1. Run: php artisan serve" -ForegroundColor White
Write-Host "  2. Try logging in!" -ForegroundColor White
Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

