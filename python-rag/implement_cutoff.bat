@echo off
echo ========================================
echo Implementing Intelligent Cutoff System
echo ========================================
echo.

echo Step 1: Backing up original context_handler.py...
copy context_handler.py context_handler_backup.py
if %errorlevel% neq 0 (
    echo ERROR: Failed to backup original file
    pause
    exit /b 1
)
echo ✓ Backup created: context_handler_backup.py
echo.

echo Step 2: Replacing with enhanced version...
copy /Y context_handler_enhanced.py context_handler.py
if %errorlevel% neq 0 (
    echo ERROR: Failed to replace file
    echo Restoring backup...
    copy /Y context_handler_backup.py context_handler.py
    pause
    exit /b 1
)
echo ✓ Enhanced version installed
echo.

echo ========================================
echo Installation Complete!
echo ========================================
echo.
echo Intelligent Cutoff System is now active.
echo.
echo Features:
echo - Relevance scoring (0-100%%)
echo - Off-topic detection (threshold: 30%%)
echo - Personalized redirects
echo - Study suggestions
echo.
echo To test, restart the RAG service:
echo   python main.py
echo.
echo To rollback:
echo   copy context_handler_backup.py context_handler.py
echo.
echo See IMPLEMENT_INTELLIGENT_CUTOFF.md for details.
echo.
pause
