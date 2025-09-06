<?php

/**
 * Environment Setup Script for Supabase Integration
 * Run this script to configure your .env file with Supabase credentials
 */

echo "🚀 College Placement Portal - Supabase Environment Setup\n";
echo "======================================================\n\n";

// Check if .env file exists
if (!file_exists('.env')) {
    echo "❌ .env file not found. Please create one first.\n";
    echo "You can copy from .env.example if it exists.\n";
    exit(1);
}

// Read current .env file
$envContent = file_get_contents('.env');

// Supabase configuration
$supabaseConfig = [
    'SUPABASE_URL' => 'https://wkqbukidxmzbgwauncrl.supabase.co',
    'SUPABASE_ANON_KEY' => 'your_supabase_anon_key_here',
    'SUPABASE_SERVICE_ROLE_KEY' => 'your_supabase_service_role_key_here',
];

// Database configuration
$dbConfig = [
    'DB_CONNECTION' => 'pgsql',
    'DB_HOST' => 'db.wkqbukidxmzbgwauncrl.supabase.co',
    'DB_PORT' => '5432',
    'DB_DATABASE' => 'postgres',
    'DB_USERNAME' => 'postgres',
    'DB_PASSWORD' => 'Supreeeth24#',
    'DB_SSLMODE' => 'require',
];

echo "📋 Current Configuration:\n";
echo "========================\n";

// Check current values
foreach (array_merge($supabaseConfig, $dbConfig) as $key => $defaultValue) {
    if (preg_match("/^{$key}=(.*)$/m", $envContent, $matches)) {
        $currentValue = trim($matches[1]);
        if ($currentValue === $defaultValue || empty($currentValue)) {
            echo "❌ {$key}: Not configured (using default)\n";
        } else {
            echo "✅ {$key}: Configured\n";
        }
    } else {
        echo "❌ {$key}: Missing from .env file\n";
    }
}

echo "\n🔧 Setup Instructions:\n";
echo "=====================\n";
echo "1. Go to your Supabase project dashboard:\n";
echo "   https://supabase.com/dashboard/project/wkqbukidxmzbgwauncrl\n\n";

echo "2. Navigate to Settings → API\n\n";

echo "3. Copy the following keys:\n";
echo "   - anon public key → SUPABASE_ANON_KEY\n";
echo "   - service_role key → SUPABASE_SERVICE_ROLE_KEY\n\n";

echo "4. Update your .env file with these values:\n\n";

echo "# Supabase Configuration\n";
echo "SUPABASE_URL=https://wkqbukidxmzbgwauncrl.supabase.co\n";
echo "SUPABASE_ANON_KEY=your_actual_anon_key_here\n";
echo "SUPABASE_SERVICE_ROLE_KEY=your_actual_service_role_key_here\n\n";

echo "# Database Configuration (already configured)\n";
echo "DB_CONNECTION=pgsql\n";
echo "DB_HOST=db.wkqbukidxmzbgwauncrl.supabase.co\n";
echo "DB_PORT=5432\n";
echo "DB_DATABASE=postgres\n";
echo "DB_USERNAME=postgres\n";
echo "DB_PASSWORD=\"Supreeeth24#\"\n";
echo "DB_SSLMODE=require\n\n";

echo "5. After updating .env, run:\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n\n";

echo "6. Test the connection:\n";
echo "   Visit: http://127.0.0.1:8000/test-db\n\n";

echo "🎯 Quick Test:\n";
echo "=============\n";
echo "Once configured, test registration at:\n";
echo "http://127.0.0.1:8000/register\n\n";

echo "📞 Need Help?\n";
echo "=============\n";
echo "If you're still having issues:\n";
echo "1. Check Laravel logs: storage/logs/laravel.log\n";
echo "2. Verify Supabase project is active\n";
echo "3. Ensure API keys are correctly copied\n\n";

echo "✅ Setup complete! Follow the instructions above to configure your API keys.\n";
