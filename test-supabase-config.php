<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\SupabaseService;

echo "=== Supabase Configuration Test ===\n\n";

// Test configuration
echo "1. Configuration Check:\n";
echo "   SUPABASE_URL: " . config('supabase.url') . "\n";
echo "   SUPABASE_ANON_KEY: " . (config('supabase.anon_key') ? 'SET' : 'NOT SET') . "\n";
echo "   SUPABASE_SERVICE_ROLE_KEY: " . (config('supabase.service_role_key') ? 'SET' : 'NOT SET') . "\n";
echo "   REDIRECT_URL: " . config('supabase.redirect_url') . "\n\n";

// Test Supabase service
echo "2. Testing Supabase Service:\n";
try {
    $supabaseService = new SupabaseService();
    echo "   ✓ SupabaseService initialized successfully\n";
} catch (Exception $e) {
    echo "   ✗ Error initializing SupabaseService: " . $e->getMessage() . "\n";
}

// Test database connection
echo "\n3. Database Connection Test:\n";
try {
    $pdo = new PDO(
        "pgsql:host=db.wkqbukidxmzbgwauncrl.supabase.co;port=5432;dbname=postgres;sslmode=require",
        "postgres",
        "Supreeeth24#"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✓ Database connection successful\n";
} catch (PDOException $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
}

// Test Supabase API (if keys are set)
echo "\n4. Supabase API Test:\n";
if (config('supabase.anon_key') && config('supabase.anon_key') !== 'your_anon_key_here') {
    try {
        $client = new GuzzleHttp\Client(['timeout' => 10]);
        $response = $client->get(config('supabase.url') . '/auth/v1/settings', [
            'headers' => [
                'apikey' => config('supabase.anon_key'),
                'Content-Type' => 'application/json',
            ]
        ]);
        echo "   ✓ Supabase API accessible\n";
    } catch (Exception $e) {
        echo "   ✗ Supabase API error: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠ Supabase API keys not configured\n";
}

echo "\n=== Test Complete ===\n";
