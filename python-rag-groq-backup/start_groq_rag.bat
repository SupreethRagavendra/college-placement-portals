@echo off
echo ========================================
echo Starting Groq RAG Service
echo ========================================
echo.

:: Check if virtual environment exists
if not exist "venv" (
    echo Creating virtual environment...
    python -m venv venv
    echo Virtual environment created.
    echo.
)

:: Activate virtual environment
echo Activating virtual environment...
call venv\Scripts\activate.bat

:: Install/upgrade dependencies
echo Installing dependencies...
pip install -q --upgrade pip
pip install -q -r requirements.txt
echo Dependencies installed.
echo.

:: Check if .env file exists
if not exist ".env" (
    if exist ".env.example" (
        echo Creating .env file from .env.example...
        copy .env.example .env
        echo.
        echo IMPORTANT: Please update .env file with your actual configuration:
        echo - GROQ_API_KEY: Your Groq API key
        echo - DB_HOST: Your Supabase host
        echo - DB_PASSWORD: Your database password
        echo.
        pause
    ) else (
        echo ERROR: .env file not found!
        echo Please create .env file with required configuration.
        pause
        exit /b 1
    )
)

:: Test Groq API connection
echo Testing Groq API connection...
python -c "from groq import Groq; import os; from dotenv import load_dotenv; load_dotenv(); client = Groq(api_key=os.getenv('GROQ_API_KEY')); print('Groq API: Connected successfully!')" 2>nul
if %errorlevel% neq 0 (
    echo WARNING: Could not connect to Groq API. Please check your API key.
    echo The service will start but may use fallback mode.
    echo.
)

:: Initialize knowledge base if needed
echo Checking knowledge base...
python -c "import chromadb; client = chromadb.PersistentClient('./chromadb_storage'); collections = client.list_collections(); print(f'Found {len(collections)} collections')"

:: Start the FastAPI service
echo.
echo ========================================
echo Starting Groq RAG Service on port 8001
echo ========================================
echo.
echo Service endpoints:
echo - Chat: http://localhost:8001/chat
echo - Health: http://localhost:8001/health
echo - Docs: http://localhost:8001/docs
echo.
echo Press Ctrl+C to stop the service
echo.

:: Run the service
python main.py

:: Deactivate on exit
deactivate
