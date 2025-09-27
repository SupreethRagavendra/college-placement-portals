# Supabase Edge Function Deployment Script for Windows PowerShell
# This script deploys the email notification Edge Function to Supabase

Write-Host "🚀 Deploying Supabase Edge Function for Email Notifications" -ForegroundColor Green
Write-Host "============================================================" -ForegroundColor Green

# Check if Supabase CLI is installed
if (-not (Get-Command "supabase" -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Supabase CLI is not installed. Installing..." -ForegroundColor Red
    npm install -g supabase
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Failed to install Supabase CLI. Please install manually:" -ForegroundColor Red
        Write-Host "   npm install -g supabase" -ForegroundColor Yellow
        exit 1
    }
}

# Check if user is logged in
Write-Host "🔐 Checking Supabase authentication..." -ForegroundColor Cyan
try {
    supabase status 2>$null
    if ($LASTEXITCODE -ne 0) {
        throw "Not logged in"
    }
} catch {
    Write-Host "🔑 Please login to Supabase:" -ForegroundColor Yellow
    supabase login
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Login failed. Please try again." -ForegroundColor Red
        exit 1
    }
}

# Check if project is linked
Write-Host "🔗 Checking project link..." -ForegroundColor Cyan
if (-not (Test-Path ".supabase/config.toml")) {
    Write-Host "📝 Project not linked. Please provide your project reference ID:" -ForegroundColor Yellow
    $project_ref = Read-Host "Project Reference ID"
    supabase link --project-ref $project_ref
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Failed to link project. Please check your project reference ID." -ForegroundColor Red
        exit 1
    }
}

# Deploy the Edge Function
Write-Host "📦 Deploying send-status-email Edge Function..." -ForegroundColor Cyan
supabase functions deploy send-status-email
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Function deployment failed. Please check your function code and try again." -ForegroundColor Red
    exit 1
}

Write-Host "✅ Edge Function deployed successfully!" -ForegroundColor Green
Write-Host ""

# Prompt for environment variables
Write-Host "🔧 Setting up environment variables..." -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan

# SendGrid API Key
Write-Host "📧 SendGrid Configuration:" -ForegroundColor Yellow
Write-Host "1. Go to https://sendgrid.com/ and create an account" -ForegroundColor White
Write-Host "2. Navigate to Settings > API Keys" -ForegroundColor White
Write-Host "3. Create a new API key with 'Mail Send' permissions" -ForegroundColor White
Write-Host ""
$sendgrid_key = Read-Host "Enter your SendGrid API Key"
if ($sendgrid_key) {
    supabase secrets set SENDGRID_API_KEY="$sendgrid_key"
    Write-Host "✅ SendGrid API key set" -ForegroundColor Green
} else {
    Write-Host "⚠️  Skipping SendGrid API key (you can set it later)" -ForegroundColor Yellow
}

# From Email
Write-Host ""
$from_email = Read-Host "Enter your FROM email address (must be verified in SendGrid)"
if ($from_email) {
    supabase secrets set FROM_EMAIL="$from_email"
    Write-Host "✅ From email set" -ForegroundColor Green
} else {
    Write-Host "⚠️  Skipping from email (you can set it later)" -ForegroundColor Yellow
}

# From Name
Write-Host ""
$from_name = Read-Host "Enter your FROM name (e.g., 'College Placement Portal')"
if ($from_name) {
    supabase secrets set FROM_NAME="$from_name"
    Write-Host "✅ From name set" -ForegroundColor Green
} else {
    Write-Host "⚠️  Skipping from name (you can set it later)" -ForegroundColor Yellow
}

# Portal URL
Write-Host ""
$portal_url = Read-Host "Enter your portal URL (e.g., https://yourportal.com)"
if ($portal_url) {
    supabase secrets set PORTAL_URL="$portal_url"
    Write-Host "✅ Portal URL set" -ForegroundColor Green
} else {
    Write-Host "⚠️  Skipping portal URL (you can set it later)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "🎉 Deployment Complete!" -ForegroundColor Green
Write-Host "=======================" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Next Steps:" -ForegroundColor Cyan
Write-Host "1. Verify your sender email in SendGrid" -ForegroundColor White
Write-Host "2. Test the function using the test script or Laravel" -ForegroundColor White
Write-Host "3. Monitor function logs: supabase functions logs send-status-email" -ForegroundColor White
Write-Host ""

# Offer to run a test
$test_choice = Read-Host "🧪 Would you like to run a test email? (y/n)"
if ($test_choice -eq "y" -or $test_choice -eq "Y") {
    Write-Host ""
    Write-Host "📧 Test Email Configuration:" -ForegroundColor Cyan
    $test_email = Read-Host "Test recipient email"
    $test_name = Read-Host "Test recipient name"
    
    Write-Host "Sending test approval email..." -ForegroundColor Cyan
    
    # Get project configuration
    try {
        $config = Get-Content ".supabase/config.toml" -Raw
        $project_url = [regex]::Match($config, 'project_url\s*=\s*"([^"]*)"').Groups[1].Value
        $anon_key = [regex]::Match($config, 'anon_key\s*=\s*"([^"]*)"').Groups[1].Value
        
        if ($project_url -and $anon_key) {
            $body = @{
                student_email = $test_email
                student_name = $test_name
                status = "approved"
            } | ConvertTo-Json
            
            $headers = @{
                "Authorization" = "Bearer $anon_key"
                "Content-Type" = "application/json"
            }
            
            Invoke-RestMethod -Uri "$project_url/functions/v1/send-status-email" -Method Post -Body $body -Headers $headers
            Write-Host ""
            Write-Host "✅ Test email sent! Check your inbox and spam folder." -ForegroundColor Green
        } else {
            Write-Host "❌ Could not retrieve project configuration for test." -ForegroundColor Red
        }
    } catch {
        Write-Host "❌ Test failed: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "📚 For more information, see: EMAIL_NOTIFICATION_SETUP.md" -ForegroundColor Cyan
Write-Host "🐛 If you encounter issues, check function logs: supabase functions logs send-status-email" -ForegroundColor Cyan