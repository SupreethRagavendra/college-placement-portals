@echo off
echo ========================================
echo Starting RAG Service (OpenRouter)
echo ========================================
echo.
echo Port: 8001
echo Mode: OpenRouter RAG with Database Sync
echo.
cd python-rag
python main.py
pause

