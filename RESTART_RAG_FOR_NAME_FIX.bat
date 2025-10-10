@echo off
echo ========================================
echo  RAG NAME UPDATE FIX - RESTART SCRIPT
echo ========================================
echo.
echo This script will restart the RAG service with the name update fix.
echo.
echo WHAT WAS FIXED:
echo   1. Laravel now sends student name/email to RAG
echo   2. Regex pattern supports all name formats (e.g., "Supreeth Ragavendra S")
echo   3. Enhanced logging for debugging
echo   4. Database updates are now verified
echo.
echo ========================================
echo.
pause
echo.
echo Stopping any existing RAG service...
taskkill /F /IM python.exe 2>nul
timeout /t 2 >nul
echo.
echo Starting RAG service with fixes...
cd /d "%~dp0python-rag"
start "RAG Service - Name Update Fix" python main.py
echo.
echo ========================================
echo  RAG SERVICE STARTED!
echo ========================================
echo.
echo The RAG service is now running in a new window.
echo.
echo TEST THE FIX:
echo   1. Open chatbot in student portal
echo   2. Type: "Change my name to Supreeth Ragavendra S"
echo   3. Press Enter
echo.
echo VERIFY:
echo   1. Chatbot should confirm: "Your profile has been updated in the database!"
echo   2. Refresh page - name should be updated in UI
echo   3. Check logs in storage\logs\laravel.log
echo.
echo Full testing guide: RAG_NAME_UPDATE_FIX.md
echo.
pause

