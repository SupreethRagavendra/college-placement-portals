@echo off
echo ============================================
echo Opening College Placement Portal
echo With NEW KIT Design (Fresh Browser)
echo ============================================
echo.

REM Start the server if not running
echo Starting Laravel server...
start /B php artisan serve

REM Wait a moment for server to start
timeout /t 3 /nobreak >nul

echo.
echo Opening browser in incognito mode to see NEW design...
echo (This bypasses browser cache)
echo.

REM Try Chrome incognito
start chrome --incognito http://localhost:8000/ 2>nul

REM If Chrome didn't work, try Edge
if errorlevel 1 (
    start msedge --inprivate http://localhost:8000/ 2>nul
)

REM If Edge didn't work, try Firefox
if errorlevel 1 (
    start firefox -private-window http://localhost:8000/ 2>nul
)

REM If nothing worked, open default browser
if errorlevel 1 (
    start http://localhost:8000/
    echo.
    echo ⚠️ Please manually clear your browser cache:
    echo Press Ctrl + Shift + Delete
)

echo.
echo ============================================
echo ✨ You should now see the NEW KIT design!
echo ============================================
echo.
echo Look for:
echo - Purple gradient navigation bar
echo - KIT College logo (top left)
echo - Orange "Get Started" button
echo - Purple and orange color scheme
echo.
echo If you still see the OLD design:
echo 1. Press Ctrl + F5 (hard refresh)
echo 2. Or read VIEW_NEW_DESIGN.md for help
echo.
pause

