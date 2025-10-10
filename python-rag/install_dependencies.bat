@echo off
echo ================================================
echo Installing Python Dependencies for RAG Service
echo ================================================
echo.

REM Activate virtual environment
call venv\Scripts\activate

echo Step 1: Upgrading pip, setuptools, wheel...
python -m pip install --upgrade pip setuptools wheel
echo.

echo Step 2: Installing core dependencies (without psycopg2)...
pip install fastapi==0.104.1
pip install "uvicorn[standard]==0.24.0"
pip install python-dotenv==1.0.0
pip install pydantic==2.5.0
pip install requests==2.31.0
pip install colorama==0.4.6
echo.

echo Step 3: Installing ChromaDB and dependencies...
pip install chromadb==0.4.22
pip install sentence-transformers==2.2.2
echo.

echo Step 4: Installing psycopg2-binary (PostgreSQL driver)...
echo This may take a moment...
pip install psycopg2-binary==2.9.9 --no-build-isolation
echo.

echo ================================================
echo Installation Complete!
echo ================================================
echo.

echo Verifying installation...
python -c "import fastapi; print('FastAPI:', fastapi.__version__)"
python -c "import uvicorn; print('Uvicorn: OK')"
python -c "import chromadb; print('ChromaDB: OK')"
python -c "import psycopg2; print('Psycopg2: OK')"
echo.

echo All dependencies installed successfully!
echo You can now run: python main.py
echo.
pause

