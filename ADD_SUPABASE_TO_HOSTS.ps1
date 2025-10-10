# Add Supabase to Windows Hosts File
# Run as Administrator!

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   ADD SUPABASE TO HOSTS FILE" -ForegroundColor Cyan
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

$hostsFile = "C:\Windows\System32\drivers\etc\hosts"
$supabaseHost = "db.wkqbukidxmzbgwauncrl.supabase.co"

Write-Host "Step 1: Resolving Supabase IP address..." -ForegroundColor Yellow

# Try to get IPv4 address from Supabase connection pooler (IPv4)
# Supabase has a connection pooler that uses IPv4
$ipv4Host = "aws-0-ap-south-1.pooler.supabase.com"

Write-Host "Trying to get IPv4 address..." -ForegroundColor Yellow

# Use .NET DNS resolution which is more reliable
try {
    # Try common Supabase IPs or resolve
    $dnsResult = [System.Net.Dns]::GetHostAddresses($supabaseHost)
    $ipAddress = $null
    
    # Prefer IPv4 if available
    foreach ($ip in $dnsResult) {
        if ($ip.AddressFamily -eq [System.Net.Sockets.AddressFamily]::InterNetwork) {
            $ipAddress = $ip.IPAddressToString
            Write-Host "Found IPv4: $ipAddress" -ForegroundColor Green
            break
        }
    }
    
    # If no IPv4, use IPv6
    if (-not $ipAddress -and $dnsResult.Count -gt 0) {
        $ipAddress = $dnsResult[0].IPAddressToString
        Write-Host "Found IPv6: $ipAddress" -ForegroundColor Yellow
        Write-Host "Warning: Using IPv6 - may not work with all applications" -ForegroundColor Yellow
    }
    
    if (-not $ipAddress) {
        throw "Could not resolve IP address"
    }
    
} catch {
    Write-Host "⚠ Could not resolve automatically. Using fallback method..." -ForegroundColor Yellow
    
    # Use nslookup to get IP
    $nslookupOutput = nslookup $supabaseHost 8.8.8.8 2>&1 | Out-String
    if ($nslookupOutput -match "Address:\s+(.+)$") {
        $ipAddress = $matches[1].Trim()
        Write-Host "Found IP from nslookup: $ipAddress" -ForegroundColor Green
    } else {
        Write-Host "❌ ERROR: Could not resolve IP address for $supabaseHost" -ForegroundColor Red
        Write-Host ""
        Write-Host "Please check your internet connection or try using a local database." -ForegroundColor Yellow
        Write-Host ""
        pause
        exit 1
    }
}

Write-Host ""
Write-Host "Step 2: Checking existing hosts file..." -ForegroundColor Yellow

# Backup hosts file
$backupFile = "$hostsFile.backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
Copy-Item $hostsFile $backupFile -Force
Write-Host "✅ Backed up to: $backupFile" -ForegroundColor Green

# Read current hosts file
$hostsContent = Get-Content $hostsFile

# Check if entry already exists
$existingEntry = $hostsContent | Select-String -Pattern $supabaseHost
if ($existingEntry) {
    Write-Host "⚠ Entry already exists in hosts file:" -ForegroundColor Yellow
    Write-Host "  $existingEntry" -ForegroundColor White
    Write-Host ""
    $response = Read-Host "Do you want to update it? (y/n)"
    if ($response -ne 'y') {
        Write-Host "Cancelled." -ForegroundColor Yellow
        pause
        exit 0
    }
    
    # Remove existing entry
    $hostsContent = $hostsContent | Where-Object { $_ -notmatch $supabaseHost }
}

Write-Host ""
Write-Host "Step 3: Adding new entry to hosts file..." -ForegroundColor Yellow

# Add new entry
$newEntry = "$ipAddress    $supabaseHost"
$hostsContent += ""
$hostsContent += "# Supabase Database (added $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss'))"
$hostsContent += $newEntry

# Write back to hosts file
$hostsContent | Set-Content $hostsFile -Force

Write-Host "✅ Added: $newEntry" -ForegroundColor Green
Write-Host ""

# Flush DNS cache
Write-Host "Step 4: Flushing DNS cache..." -ForegroundColor Yellow
ipconfig /flushdns | Out-Null
Write-Host "✅ DNS cache flushed" -ForegroundColor Green
Write-Host ""

# Test connection
Write-Host "Step 5: Testing connection..." -ForegroundColor Yellow
Set-Location $PSScriptRoot
$phpTest = php test-db-connection.php 2>&1 | Out-String
Write-Host $phpTest

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   COMPLETE!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Now try logging in to your application!" -ForegroundColor Green
Write-Host ""
Write-Host "If still not working, you may need to restart PHP/web server" -ForegroundColor Yellow
Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

