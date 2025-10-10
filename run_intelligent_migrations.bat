@echo off
echo ================================================
echo Running Intelligent Chatbot Migrations
echo ================================================
echo.

REM Run the new migrations
php artisan migrate --path=database/migrations/2025_01_05_000001_create_chatbot_conversations_table.php
php artisan migrate --path=database/migrations/2025_01_05_000002_create_chatbot_messages_table.php
php artisan migrate --path=database/migrations/2025_01_05_000003_create_student_performance_analytics_table.php
php artisan migrate --path=database/migrations/2025_01_05_000004_create_chatbot_intents_table.php

echo.
echo ================================================
echo Migrations completed successfully!
echo ================================================
echo.

REM Show the current migration status
php artisan migrate:status

pause
