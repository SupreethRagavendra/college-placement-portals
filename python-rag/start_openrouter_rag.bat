@echo off
echo ================================================
echo Starting OpenRouter RAG Service
echo ================================================
echo.

REM Check if virtual environment exists
if not exist "venv" (
    echo Creating virtual environment...
    python -m venv venv
    echo.
)

REM Activate virtual environment
echo Activating virtual environment...
call venv\Scripts\activate
echo.

REM Install/update dependencies
echo Installing dependencies...
pip install -r requirements.txt --quiet
echo.

REM Copy config if .env doesn't exist
if not exist ".env" (
    if exist "config.env" (
        echo Creating .env from config.env...
        copy config.env .env
    )
)

echo ================================================
echo Starting RAG Service on port 8001...
echo ================================================
echo.
echo Primary Model: Qwen 2.5 72B Instruct (free)
echo Fallback Model: DeepSeek V3.1 (free)
echo Provider: OpenRouter AI
echo.
echo Service will be available at: http://localhost:8001
echo Health Check: http://localhost:8001/health
echo.
echo Press Ctrl+C to stop the service
echo ================================================
echo.

python main.py

