@echo off
echo ========================================
echo   SWITCH TO LOCAL DATABASE (SQLite)
echo ========================================
echo.
echo This will switch to a local database to avoid DNS issues
echo.

cd /d "%~dp0"

echo Step 1: Backing up .env...
copy /Y .env .env.backup_before_sqlite >nul 2>&1
echo Backed up .env
echo.

echo Step 2: Creating SQLite database file...
if not exist "database" mkdir database
type nul > database\database.sqlite
echo Created database\database.sqlite
echo.

echo Step 3: Updating .env for SQLite...
powershell -Command "$content = Get-Content .env -Raw; $content = $content -replace 'DB_CONNECTION=pgsql', 'DB_CONNECTION=sqlite'; $content = $content -replace 'DB_HOST=.*', '#DB_HOST=db.wkqbukidxmzbgwauncrl.supabase.co'; $content = $content -replace 'DB_PORT=.*', '#DB_PORT=5432'; $content = $content -replace 'DB_DATABASE=postgres', 'DB_DATABASE=database/database.sqlite'; $content = $content -replace 'DB_USERNAME=.*', '#DB_USERNAME=postgres'; $content = $content -replace 'DB_PASSWORD=.*', '#DB_PASSWORD='; if ($content -notmatch 'DB_CONNECTION=sqlite') { $content += \"`nDB_CONNECTION=sqlite`nDB_DATABASE=database/database.sqlite\" }; $content | Set-Content .env"
echo Updated .env to use SQLite
echo.

echo Step 4: Clearing Laravel caches...
php artisan config:clear
php artisan cache:clear
echo Caches cleared
echo.

echo Step 5: Running migrations...
echo This will create all tables in the local database...
php artisan migrate:fresh --seed
echo.

echo ========================================
echo   SWITCH COMPLETE!
echo ========================================
echo.
echo Your application now uses a local SQLite database
echo No internet connection required!
echo.
echo To restore Supabase later, restore from:
echo   .env.backup_before_sqlite
echo.
echo Now run: php artisan serve
echo Then try logging in!
echo.
pause

