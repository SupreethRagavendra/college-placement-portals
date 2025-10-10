# PowerShell Script to Fix Supabase DNS Issues
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "  FIXING SUPABASE DNS ISSUE" -ForegroundColor Cyan  
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Check if running as Administrator
if (-NOT ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator"))
{
    Write-Host "This script needs Administrator privileges!" -ForegroundColor Red
    Write-Host "Please run PowerShell as Administrator and try again." -ForegroundColor Yellow
    pause
    exit
}

Write-Host "Step 1: Clearing DNS Cache..." -ForegroundColor Yellow
Clear-DnsClientCache
Write-Host "✅ DNS cache cleared" -ForegroundColor Green
Write-Host ""

Write-Host "Step 2: Testing current DNS resolution..." -ForegroundColor Yellow
$hostname = "db.wkqbukidxmzbgwauncrl.supabase.co"
try {
    $result = Resolve-DnsName -Name $hostname -ErrorAction Stop
    Write-Host "✅ DNS resolution working! IP: $($result.IPAddress)" -ForegroundColor Green
} catch {
    Write-Host "❌ Current DNS cannot resolve Supabase" -ForegroundColor Red
    Write-Host ""
    
    Write-Host "Step 3: Configuring Google DNS..." -ForegroundColor Yellow
    
    # Get all network adapters
    $adapters = Get-NetAdapter | Where-Object {$_.Status -eq "Up"}
    
    foreach ($adapter in $adapters) {
        Write-Host "  Configuring $($adapter.Name)..." -ForegroundColor Gray
        Set-DnsClientServerAddress -InterfaceAlias $adapter.Name -ServerAddresses "8.8.8.8", "8.8.4.4" -ErrorAction SilentlyContinue
    }
    
    Write-Host "✅ Google DNS configured" -ForegroundColor Green
    Write-Host ""
    
    Write-Host "Step 4: Testing with Google DNS..." -ForegroundColor Yellow
    try {
        $result = Resolve-DnsName -Name $hostname -Server "8.8.8.8" -ErrorAction Stop
        Write-Host "✅ Google DNS can resolve! IP: $($result.IPAddress)" -ForegroundColor Green
        
        # Add to hosts file as backup
        Write-Host ""
        Write-Host "Step 5: Adding to hosts file as backup..." -ForegroundColor Yellow
        $hostsPath = "$env:windir\System32\drivers\etc\hosts"
        $ip = $result.IPAddress | Select-Object -First 1
        $entry = "$ip $hostname"
        
        # Remove old entry if exists
        $content = Get-Content $hostsPath | Where-Object {$_ -notmatch $hostname}
        $content += ""
        $content += "# Supabase Database (added by fix script)"
        $content += $entry
        
        Set-Content -Path $hostsPath -Value $content -Force
        Write-Host "✅ Added to hosts file: $entry" -ForegroundColor Green
        
    } catch {
        Write-Host "❌ Even Google DNS cannot resolve. Check internet connection!" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "         FIX COMPLETE!" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Now try running: php artisan serve" -ForegroundColor Yellow
Write-Host ""
pause
