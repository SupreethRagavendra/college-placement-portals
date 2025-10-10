# PowerShell script to test chatbot three-mode system
# Run this from project root: .\test_chatbot_modes.ps1

Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  THREE-MODE CHATBOT TESTING GUIDE" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Check if Laravel is running
Write-Host "🔍 Checking Laravel server..." -ForegroundColor Yellow
$laravelRunning = $false
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000" -Method GET -TimeoutSec 2 -ErrorAction SilentlyContinue
    if ($response.StatusCode -eq 200) {
        $laravelRunning = $true
        Write-Host "✅ Laravel is RUNNING on http://localhost:8000" -ForegroundColor Green
    }
} catch {
    Write-Host "❌ Laravel is NOT running" -ForegroundColor Red
}

Write-Host ""

# Check if RAG service is running
Write-Host "🔍 Checking RAG service..." -ForegroundColor Yellow
$ragRunning = $false
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8001/health" -Method GET -TimeoutSec 2 -ErrorAction SilentlyContinue
    if ($response.StatusCode -eq 200) {
        $ragRunning = $true
        Write-Host "✅ RAG service is RUNNING on http://localhost:8001" -ForegroundColor Green
    }
} catch {
    Write-Host "❌ RAG service is NOT running" -ForegroundColor Red
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  CURRENT MODE" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Determine current mode
if ($laravelRunning -and $ragRunning) {
    Write-Host "🟢 MODE 1: RAG ACTIVE" -ForegroundColor Green
    Write-Host "   Status: Full AI-powered responses" -ForegroundColor Green
    Write-Host "   Features: Vector search, context-aware, semantic understanding" -ForegroundColor Gray
    Write-Host "   Header Color: GREEN" -ForegroundColor Green
    Write-Host ""
    Write-Host "✅ EXPECTED BEHAVIOR:" -ForegroundColor Cyan
    Write-Host "   - Chatbot header will be GREEN" -ForegroundColor Gray
    Write-Host "   - AI-generated intelligent responses" -ForegroundColor Gray
    Write-Host "   - 2-4 second response time" -ForegroundColor Gray
    Write-Host "   - High quality contextual answers" -ForegroundColor Gray
}
elseif ($laravelRunning -and -not $ragRunning) {
    Write-Host "🟡 MODE 2: LIMITED MODE" -ForegroundColor Yellow
    Write-Host "   Status: Database queries with pattern matching" -ForegroundColor Yellow
    Write-Host "   Features: Real assessment data, result queries, navigation" -ForegroundColor Gray
    Write-Host "   Header Color: YELLOW" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "✅ EXPECTED BEHAVIOR:" -ForegroundColor Cyan
    Write-Host "   - Chatbot header will be YELLOW" -ForegroundColor Gray
    Write-Host "   - Shows ACTUAL assessments from database" -ForegroundColor Gray
    Write-Host "   - Shows ACTUAL results and scores" -ForegroundColor Gray
    Write-Host "   - Pattern-based responses (not AI)" -ForegroundColor Gray
    Write-Host "   - <1 second response time" -ForegroundColor Gray
    Write-Host ""
    Write-Host "📝 TRY THESE QUERIES:" -ForegroundColor Magenta
    Write-Host "   • 'What assessments are available?'" -ForegroundColor White
    Write-Host "   • 'Show my results'" -ForegroundColor White
    Write-Host "   • 'What's my score?'" -ForegroundColor White
}
elseif (-not $laravelRunning) {
    Write-Host "🔴 MODE 3: OFFLINE MODE" -ForegroundColor Red
    Write-Host "   Status: Frontend-only fallback" -ForegroundColor Red
    Write-Host "   Features: Static responses, no database access" -ForegroundColor Gray
    Write-Host "   Header Color: RED" -ForegroundColor Red
    Write-Host ""
    Write-Host "✅ EXPECTED BEHAVIOR:" -ForegroundColor Cyan
    Write-Host "   - Chatbot header will be RED" -ForegroundColor Gray
    Write-Host "   - Shows offline warning messages" -ForegroundColor Gray
    Write-Host "   - No real data available" -ForegroundColor Gray
    Write-Host "   - Instant response (no backend call)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  TESTING INSTRUCTIONS" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

if (-not $laravelRunning) {
    Write-Host "⚠️  TO TEST MODE 2 (LIMITED MODE):" -ForegroundColor Yellow
    Write-Host "   1. Start Laravel: php artisan serve" -ForegroundColor White
    Write-Host "   2. Keep RAG service STOPPED" -ForegroundColor White
    Write-Host "   3. Login as student and open chatbot" -ForegroundColor White
    Write-Host "   4. Ask: 'What assessments are available?'" -ForegroundColor White
    Write-Host "   5. Expect: YELLOW header with real assessment list" -ForegroundColor White
    Write-Host ""
}

if ($laravelRunning -and -not $ragRunning) {
    Write-Host "✅ PERFECT! You're in MODE 2 (LIMITED MODE)" -ForegroundColor Green
    Write-Host "   This is what we fixed!" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "📋 VERIFICATION STEPS:" -ForegroundColor Magenta
    Write-Host "   1. Login to: http://localhost:8000" -ForegroundColor White
    Write-Host "   2. Click chatbot button (bottom-right)" -ForegroundColor White
    Write-Host "   3. Verify header is YELLOW" -ForegroundColor White
    Write-Host "   4. Ask: 'What assessments are available?'" -ForegroundColor White
    Write-Host "   5. Verify you see REAL assessment names from database" -ForegroundColor White
    Write-Host "   6. Check logs: tail -f storage/logs/laravel.log | grep MODE" -ForegroundColor Gray
    Write-Host ""
}

if ($laravelRunning -and $ragRunning) {
    Write-Host "✅ PERFECT! You're in MODE 1 (RAG ACTIVE)" -ForegroundColor Green
    Write-Host ""
    Write-Host "🔄 TO TEST MODE 2 (LIMITED MODE):" -ForegroundColor Yellow
    Write-Host "   1. Stop RAG service (Ctrl+C in RAG terminal)" -ForegroundColor White
    Write-Host "   2. Keep Laravel running" -ForegroundColor White
    Write-Host "   3. Refresh chatbot" -ForegroundColor White
    Write-Host "   4. Verify header turns YELLOW" -ForegroundColor White
    Write-Host "   5. Ask: 'What assessments are available?'" -ForegroundColor White
    Write-Host "   6. Expect: Database query results with real assessments" -ForegroundColor White
    Write-Host ""
}

Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  QUICK COMMANDS" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "Start Laravel:" -ForegroundColor Yellow
Write-Host "  php artisan serve" -ForegroundColor White
Write-Host ""
Write-Host "Start RAG Service:" -ForegroundColor Yellow
Write-Host "  cd python-rag" -ForegroundColor White
Write-Host "  python rag_service.py" -ForegroundColor White
Write-Host ""
Write-Host "Watch Logs:" -ForegroundColor Yellow
Write-Host "  Get-Content storage\logs\laravel.log -Wait -Tail 20" -ForegroundColor White
Write-Host ""
Write-Host "Check Health:" -ForegroundColor Yellow
Write-Host "  Invoke-WebRequest http://localhost:8000/student/health" -ForegroundColor White
Write-Host ""

Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
