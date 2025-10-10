# Quick Session/Login Fix for "Works in Incognito" Issue
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   FIXING SESSION/LOGIN ISSUE" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Navigate to script directory
Set-Location $PSScriptRoot

# Clear session files
Write-Host "Clearing session files..." -ForegroundColor Yellow
if (Test-Path "storage\framework\sessions") {
    Remove-Item "storage\framework\sessions\*" -Force -ErrorAction SilentlyContinue
    Write-Host "✅ Session files cleared" -ForegroundColor Green
}

# Clear cache files
Write-Host "Clearing cache files..." -ForegroundColor Yellow
Remove-Item "bootstrap\cache\config.php" -Force -ErrorAction SilentlyContinue
Remove-Item "bootstrap\cache\routes*.php" -Force -ErrorAction SilentlyContinue
Remove-Item "bootstrap\cache\services.php" -Force -ErrorAction SilentlyContinue
Write-Host "✅ Cache files cleared" -ForegroundColor Green

# Clear Laravel caches (ignore errors if DB not available)
Write-Host "Clearing Laravel caches..." -ForegroundColor Yellow
php artisan config:clear 2>&1 | Out-Null
php artisan view:clear 2>&1 | Out-Null
php artisan route:clear 2>&1 | Out-Null
Write-Host "✅ Laravel caches cleared" -ForegroundColor Green

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   NOW CLEAR YOUR BROWSER!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Quick Method: Hard Refresh" -ForegroundColor Yellow
Write-Host "  1. Go to login page" -ForegroundColor White
Write-Host "  2. Press: Ctrl + Shift + R" -ForegroundColor White
Write-Host "     (or Ctrl + F5)" -ForegroundColor White
Write-Host ""
Write-Host "OR" -ForegroundColor Yellow
Write-Host ""
Write-Host "Clear All Browser Data:" -ForegroundColor Yellow
Write-Host "  1. Press: Ctrl + Shift + Delete" -ForegroundColor White
Write-Host "  2. Select 'Cookies' and 'Cached images'" -ForegroundColor White
Write-Host "  3. Time range: All time" -ForegroundColor White
Write-Host "  4. Click 'Clear data'" -ForegroundColor White
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "After clearing browser, try logging in!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

