@echo off
echo ================================================================
echo RESTARTING ALL SERVICES TO FIX CHATBOT
echo ================================================================
echo.

echo Step 1: Finding and killing RAG service on port 8001...
for /f "tokens=5" %%a in ('netstat -aon ^| find ":8001" ^| find "LISTENING"') do (
    echo Killing process %%a
    taskkill /F /PID %%a
)

echo.
echo Step 2: Starting RAG service with NEW code...
cd python-rag
start "RAG Service" cmd /k "python simple_rag_service.py"

echo.
echo Waiting 5 seconds for RAG to start...
timeout /t 5 /nobreak

echo.
echo Step 3: Testing RAG service...
curl http://localhost:8001

echo.
echo.
echo ================================================================
echo SERVICES RESTARTED!
echo ================================================================
echo.
echo RAG Service: Running in new window
echo.
echo NOW DO THIS:
echo 1. Close your browser completely
echo 2. Reopen browser in Incognito mode (Ctrl+Shift+N)
echo 3. Go to http://localhost:8000
echo 4. Login and test chatbot
echo.
echo Ask: "Which assessments have I completed?"
echo.
echo Expected: Shows "Technical Assessment - Programming Fundamentals: 1/2 (50%)"
echo ================================================================
pause

