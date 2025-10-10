# Add Supabase to Hosts File - IMMEDIATE FIX
# Run as Administrator!

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   ADDING SUPABASE TO HOSTS FILE" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check for admin rights
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "❌ ERROR: This script must be run as Administrator!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please:" -ForegroundColor Yellow
    Write-Host "  1. Right-click on this file" -ForegroundColor White
    Write-Host "  2. Select 'Run with PowerShell'" -ForegroundColor White
    Write-Host "  3. Click 'Yes' when prompted for admin rights" -ForegroundColor White
    Write-Host ""
    pause
    exit 1
}

Write-Host "✅ Running as Administrator" -ForegroundColor Green
Write-Host ""

$hostsFile = "C:\Windows\System32\drivers\etc\hosts"
$supabaseHost = "db.wkqbukidxmzbgwauncrl.supabase.co"
$ipAddress = "2406:da18:243:740a:6d30:33f9:31ae:d2fa"

# Backup hosts file
$backupFile = "$hostsFile.backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
Write-Host "Step 1: Backing up hosts file..." -ForegroundColor Yellow
Copy-Item $hostsFile $backupFile -Force
Write-Host "✅ Backed up to: $backupFile" -ForegroundColor Green
Write-Host ""

# Read current hosts
Write-Host "Step 2: Reading current hosts file..." -ForegroundColor Yellow
$hostsContent = Get-Content $hostsFile

# Check if entry exists
$existingEntry = $hostsContent | Where-Object { $_ -match $supabaseHost -and $_ -notmatch "^#" }
if ($existingEntry) {
    Write-Host "⚠ Removing existing entry: $existingEntry" -ForegroundColor Yellow
    $hostsContent = $hostsContent | Where-Object { $_ -notmatch $supabaseHost -or $_ -match "^#" }
}
Write-Host "✅ Hosts file read" -ForegroundColor Green
Write-Host ""

# Add new entry
Write-Host "Step 3: Adding Supabase entry..." -ForegroundColor Yellow
$newEntry = "$ipAddress    $supabaseHost"
$hostsContent += ""
$hostsContent += "# Supabase Database (added $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss'))"
$hostsContent += $newEntry

# Write back
$hostsContent | Set-Content $hostsFile -Force
Write-Host "✅ Added: $newEntry" -ForegroundColor Green
Write-Host ""

# Flush DNS
Write-Host "Step 4: Flushing DNS cache..." -ForegroundColor Yellow
ipconfig /flushdns | Out-Null
Write-Host "✅ DNS cache flushed" -ForegroundColor Green
Write-Host ""

# Test connection
Write-Host "Step 5: Testing database connection..." -ForegroundColor Yellow
Set-Location $PSScriptRoot
Write-Host ""
php test-db-connection.php
Write-Host ""

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   FIX APPLIED!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "If the test shows 'SUCCESS', you're good to go!" -ForegroundColor Green
Write-Host ""
Write-Host "If it still fails:" -ForegroundColor Yellow
Write-Host "  1. Your system may not support IPv6" -ForegroundColor White
Write-Host "  2. PHP may not be compiled with IPv6 support" -ForegroundColor White
Write-Host "  3. You may need to restart your computer" -ForegroundColor White
Write-Host ""
Write-Host "Now try:" -ForegroundColor Green
Write-Host "  php artisan serve" -ForegroundColor White
Write-Host "  Then login!" -ForegroundColor White
Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

