@echo off
echo ========================================
echo Starting RAG Service (OpenRouter)
echo ========================================
echo.
echo Port: 8001
echo Mode: OpenRouter RAG with Database Sync
echo.
echo Starting in new window...
echo.

start "RAG Service - Port 8001" cmd /k "cd python-rag && python main.py"

echo.
echo ========================================
echo RAG Service Started!
echo ========================================
echo.
echo Service is running in a new window
echo Port: http://localhost:8001
echo.
echo To test:
echo   1. Open browser: http://localhost:8001/health
echo   2. You should see: {"status":"healthy"}
echo.
echo To stop: Close the RAG Service window
echo.
pause

