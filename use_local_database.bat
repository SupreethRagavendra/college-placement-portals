@echo off
echo =============================================
echo    SWITCHING TO LOCAL SQLite DATABASE
echo =============================================
echo.

echo Creating local SQLite database...
echo.

REM Create local database file
type nul > database\database.sqlite
echo ✅ Created database\database.sqlite
echo.

echo Updating .env to use SQLite...
echo.

REM Create backup of .env
copy .env .env.backup_supabase >nul 2>&1
echo ✅ Backed up .env to .env.backup_supabase
echo.

REM Create new .env for SQLite
echo # Temporary SQLite configuration > .env.sqlite
echo # Original .env backed up to .env.backup_supabase >> .env.sqlite
echo. >> .env.sqlite
echo APP_NAME=Laravel >> .env.sqlite
echo APP_ENV=local >> .env.sqlite
echo APP_KEY=%APP_KEY% >> .env.sqlite
echo APP_DEBUG=true >> .env.sqlite
echo APP_URL=http://localhost:8000 >> .env.sqlite
echo. >> .env.sqlite
echo # Using SQLite temporarily >> .env.sqlite
echo DB_CONNECTION=sqlite >> .env.sqlite
echo DB_DATABASE=%cd%\database\database.sqlite >> .env.sqlite
echo. >> .env.sqlite
echo # Original Supabase config (commented out) >> .env.sqlite
echo # DB_CONNECTION=pgsql >> .env.sqlite
echo # DB_HOST=db.wkqbukidxmzbgwauncrl.supabase.co >> .env.sqlite
echo # DB_PORT=5432 >> .env.sqlite
echo # DB_DATABASE=postgres >> .env.sqlite
echo # DB_USERNAME=postgres >> .env.sqlite
echo # DB_PASSWORD=[your-password] >> .env.sqlite
echo. >> .env.sqlite

echo To use SQLite temporarily:
echo 1. Copy contents from .env.sqlite to .env
echo 2. Run: php artisan migrate:fresh --seed
echo 3. Run: php artisan serve
echo.
echo To restore Supabase later:
echo - Copy .env.backup_supabase back to .env
echo.
pause
