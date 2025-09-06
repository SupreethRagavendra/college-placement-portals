<?php

/**
 * Test Registration Process
 * This script will help identify registration issues
 */

require_once 'vendor/autoload.php';

use App\Services\SupabaseService;
use Illuminate\Support\Facades\Config;

// Load Laravel configuration
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Registration Test\n";
echo "==================\n\n";

// Test Supabase configuration
echo "1. Testing Supabase Configuration:\n";
echo "   URL: " . config('supabase.url') . "\n";
echo "   Anon Key: " . (str_contains(config('supabase.anon_key'), 'your_anon_key_here') ? '❌ NOT CONFIGURED' : '✅ CONFIGURED') . "\n";
echo "   Service Key: " . (str_contains(config('supabase.service_role_key'), 'your_service_role_key_here') ? '❌ NOT CONFIGURED' : '✅ CONFIGURED') . "\n\n";

// Test Supabase connection
echo "2. Testing Supabase Connection:\n";
try {
    $supabaseService = new SupabaseService();
    
    // Test with a dummy request to see if we can reach Supabase
    $client = new \GuzzleHttp\Client(['timeout' => 10, 'verify' => false]);
    $response = $client->get(config('supabase.url') . '/rest/v1/', [
        'headers' => [
            'apikey' => config('supabase.anon_key'),
            'Authorization' => 'Bearer ' . config('supabase.anon_key'),
        ]
    ]);
    
    echo "   ✅ Supabase connection successful\n";
    echo "   Status: " . $response->getStatusCode() . "\n\n";
    
} catch (\Exception $e) {
    echo "   ❌ Supabase connection failed: " . $e->getMessage() . "\n\n";
}

// Test registration with dummy data
echo "3. Testing Registration Process:\n";
try {
    $testEmail = 'test' . time() . '@example.com';
    $testPassword = 'TestPassword123!';
    
    echo "   Testing with email: {$testEmail}\n";
    
    $supabaseService = new SupabaseService();
    $result = $supabaseService->signUp($testEmail, $testPassword, [
        'name' => 'Test User',
        'role' => 'student'
    ]);
    
    echo "   ✅ Registration successful!\n";
    echo "   User ID: " . ($result['user']['id'] ?? 'N/A') . "\n";
    echo "   Email Confirmed: " . ($result['user']['email_confirmed_at'] ? 'Yes' : 'No') . "\n\n";
    
} catch (\Exception $e) {
    echo "   ❌ Registration failed: " . $e->getMessage() . "\n\n";
}

// Check database connection
echo "4. Testing Database Connection:\n";
try {
    $pdo = new PDO(
        "pgsql:host=" . config('database.connections.pgsql.host') . 
        ";port=" . config('database.connections.pgsql.port') . 
        ";dbname=" . config('database.connections.pgsql.database') . 
        ";sslmode=require",
        config('database.connections.pgsql.username'),
        config('database.connections.pgsql.password')
    );
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
    $studentCount = $stmt->fetchColumn();
    
    echo "   ✅ Database connection successful\n";
    echo "   Current students in database: {$studentCount}\n\n";
    
} catch (\Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n\n";
}

echo "🎯 Recommendations:\n";
echo "==================\n";
echo "1. If Supabase connection fails, check your API keys\n";
echo "2. If registration fails, check Supabase project settings\n";
echo "3. If database fails, check your database credentials\n";
echo "4. Check Laravel logs: storage/logs/laravel.log\n\n";

echo "✅ Test complete!\n";
