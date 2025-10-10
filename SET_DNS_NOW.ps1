# Set Google DNS - Run as Administrator
# This MUST be run as admin to work

$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "❌ Must run as Administrator!" -ForegroundColor Red
    pause
    exit 1
}

Write-Host "Setting Google DNS on all active network adapters..." -ForegroundColor Yellow
Write-Host ""

$adapters = Get-NetAdapter | Where-Object {$_.Status -eq "Up"}
foreach ($adapter in $adapters) {
    try {
        Write-Host "Setting DNS for: $($adapter.Name)..." -ForegroundColor Cyan
        Set-DnsClientServerAddress -InterfaceAlias $adapter.Name -ServerAddresses ("8.8.8.8","8.8.4.4") -ErrorAction Stop
        Write-Host "  ✅ Success!" -ForegroundColor Green
    } catch {
        Write-Host "  ❌ Failed: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "Flushing DNS cache..." -ForegroundColor Yellow
ipconfig /flushdns | Out-Null
ipconfig /registerdns | Out-Null
Write-Host "✅ DNS cache flushed" -ForegroundColor Green

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   DNS CONFIGURED!" -ForegroundColor Cyan  
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "NOW YOU MUST RESTART YOUR COMPUTER!" -ForegroundColor Yellow -BackgroundColor Red
Write-Host ""
Write-Host "Network changes require a full restart to take effect." -ForegroundColor Yellow
Write-Host ""
Write-Host "After restart:" -ForegroundColor Green
Write-Host "  1. cd D:\project-mini\college-placement-portal" -ForegroundColor White
Write-Host "  2. php artisan serve" -ForegroundColor White
Write-Host "  3. Try logging in!" -ForegroundColor White
Write-Host ""
pause

