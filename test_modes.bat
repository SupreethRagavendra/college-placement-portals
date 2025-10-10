@echo off
setlocal enabledelayedexpansion

echo ===============================================================
echo   THREE-MODE CHATBOT TESTING TOOL
echo ===============================================================
echo.

:: Check Laravel
echo [1/2] Checking Laravel server...
curl -s http://localhost:8000 >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] Laravel is RUNNING on http://localhost:8000
    set LARAVEL_STATUS=running
) else (
    echo [FAIL] Laravel is NOT running
    set LARAVEL_STATUS=down
)
echo.

:: Check RAG Service
echo [2/2] Checking RAG service...
curl -s http://localhost:8001/health >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] RAG service is RUNNING on http://localhost:8001
    set RAG_STATUS=running
) else (
    echo [FAIL] RAG service is NOT running
    set RAG_STATUS=down
)
echo.

echo ===============================================================
echo   CURRENT MODE DETERMINATION
echo ===============================================================
echo.

:: Determine mode
if "%LARAVEL_STATUS%"=="running" (
    if "%RAG_STATUS%"=="running" (
        echo Current Mode: MODE 1 - RAG ACTIVE
        echo Status: Full AI-powered responses
        echo Color: GREEN
        echo.
        echo EXPECTED BEHAVIOR:
        echo  - Chatbot header will be GREEN
        echo  - AI-generated intelligent responses
        echo  - 2-4 second response time
        echo  - High quality contextual answers
    ) else (
        echo Current Mode: MODE 2 - LIMITED MODE
        echo Status: Database queries with pattern matching
        echo Color: YELLOW
        echo.
        echo EXPECTED BEHAVIOR:
        echo  - Chatbot header will be YELLOW
        echo  - Shows ACTUAL assessments from database
        echo  - Shows ACTUAL results and scores
        echo  - Pattern-based responses (not AI^)
        echo  - Less than 1 second response time
        echo.
        echo TRY THESE QUERIES:
        echo  * 'What assessments are available?'
        echo  * 'Show my results'
        echo  * 'What's my score?'
    )
) else (
    echo Current Mode: MODE 3 - OFFLINE MODE
    echo Status: Frontend-only fallback
    echo Color: RED
    echo.
    echo EXPECTED BEHAVIOR:
    echo  - Chatbot header will be RED
    echo  - Shows offline warning messages
    echo  - No real data available
    echo  - Instant response (no backend call^)
    echo.
    echo TO FIX:
    echo  1. Start Laravel: php artisan serve
)

echo.
echo ===============================================================
echo   QUICK COMMANDS
echo ===============================================================
echo.
echo Start Laravel:
echo   php artisan serve
echo.
echo Start RAG Service:
echo   cd python-rag ^&^& python rag_service.py
echo.
echo Check Mode via API:
echo   curl http://localhost:8000/student/chatbot-mode-test
echo.
echo Watch Logs:
echo   powershell Get-Content storage\logs\laravel.log -Wait -Tail 20
echo.
echo ===============================================================
echo.

:: Offer to open browser
if "%LARAVEL_STATUS%"=="running" (
    set /p OPEN_BROWSER="Open browser to test chatbot? (y/n): "
    if /i "!OPEN_BROWSER!"=="y" (
        echo Opening browser...
        start http://localhost:8000/login
    )
)

pause
