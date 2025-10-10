@echo off
echo ================================================
echo RESTARTING RAG SERVICE WITH NEW CODE
echo ================================================
echo.

echo Step 1: Stopping any running Python RAG processes...
taskkill /F /FI "WINDOWTITLE eq *main.py*" 2>nul
taskkill /F /FI "IMAGENAME eq python.exe" /FI "MEMUSAGE gt 50000" 2>nul
timeout /t 2 /nobreak >nul

echo.
echo Step 2: Starting RAG service with updated code...
cd python-rag
start "RAG Service" python main.py

echo.
echo ================================================
echo RAG Service Restarted!
echo ================================================
echo.
echo The RAG service is now running with:
echo - Fixed database query (no fake assessments)
echo - No signatures in responses
echo - Student-specific filtering
echo.
echo Test in chatbot now!
echo.
pause

