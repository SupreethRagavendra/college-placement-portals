@echo off
REM Startup script for Groq AI RAG Service (Windows)
REM This script starts the RAG service on port 8001

echo ============================================================
echo Starting Groq AI RAG Service for College Placement Portal
echo ============================================================
echo.

REM Check if .env file exists
if not exist .env (
    echo [ERROR] .env file not found!
    echo Please copy .env.example to .env and configure it.
    echo.
    echo Run: copy .env.example .env
    echo Then edit .env with your database credentials
    pause
    exit /b 1
)

REM Check if virtual environment exists
if not exist venv (
    echo [INFO] Virtual environment not found. Creating...
    python -m venv venv
    if errorlevel 1 (
        echo [ERROR] Failed to create virtual environment
        pause
        exit /b 1
    )
)

REM Activate virtual environment
echo [INFO] Activating virtual environment...
call venv\Scripts\activate.bat

REM Install/upgrade dependencies
echo [INFO] Installing dependencies...
pip install -r requirements.txt --quiet
if errorlevel 1 (
    echo [WARNING] Some dependencies may have failed to install
)

REM Check if ChromaDB storage exists
if not exist chromadb_storage (
    echo.
    echo [WARNING] ChromaDB storage not found!
    echo Please initialize the knowledge base first:
    echo   python init_knowledge_groq.py
    echo.
    set /p INIT="Do you want to initialize now? (y/n): "
    if /i "%INIT%"=="y" (
        echo [INFO] Initializing knowledge base...
        python init_knowledge_groq.py
        if errorlevel 1 (
            echo [ERROR] Knowledge base initialization failed
            pause
            exit /b 1
        )
    ) else (
        echo [WARNING] Starting without initialized knowledge base
    )
)

echo.
echo ============================================================
echo Starting RAG Service on http://localhost:8001
echo ============================================================
echo Press Ctrl+C to stop the service
echo.

REM Start the service
python main.py

REM If service exits
echo.
echo [INFO] RAG Service stopped
pause
