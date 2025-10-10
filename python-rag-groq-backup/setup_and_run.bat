@echo off
echo ============================================================
echo Setting up Groq RAG Service

REM Check if virtual environment exists
if not exist ".venv" (
    echo Creating virtual environment...
    python -m venv .venv
)

REM Activate virtual environment
call .venv\Scripts\activate.bat

REM Copy config to .env if .env doesn't exist
if not exist ".env" (
    echo Creating .env file from config.env...
    copy config.env .env
)

REM Install dependencies
echo Installing dependencies...
pip install fastapi uvicorn groq python-dotenv chromadb sentence-transformers python-multipart httpx orjson

REM Try to install psycopg2-binary (optional)
echo Installing PostgreSQL adapter...
pip install psycopg2-binary || echo "Warning: psycopg2-binary failed to install, continuing without it..."

REM Initialize knowledge base
echo Initializing knowledge base...
python init_knowledge_base.py

echo.
echo ============================================================
echo Setup Complete! Starting RAG Service...
echo ============================================================
echo.

python main.py

pause
