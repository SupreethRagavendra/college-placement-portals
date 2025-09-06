<?php

/**
 * Debug Supabase Response Structure
 */

require_once 'vendor/autoload.php';

use App\Services\SupabaseService;

// Load Laravel configuration
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Debugging Supabase Response Structure\n";
echo "=======================================\n\n";

try {
    $supabaseService = new SupabaseService();
    
    // Test with a unique email
    $testEmail = 'test' . time() . '@gmail.com';
    $testPassword = 'TestPassword123!';
    
    echo "Testing registration with:\n";
    echo "Email: {$testEmail}\n";
    echo "Password: {$testPassword}\n\n";
    
    $result = $supabaseService->signUp($testEmail, $testPassword, [
        'name' => 'Test User',
        'role' => 'student'
    ]);
    
    echo "📋 Full Response Structure:\n";
    echo "==========================\n";
    print_r($result);
    echo "\n";
    
    echo "📋 Response Keys:\n";
    echo "================\n";
    if (is_array($result)) {
        foreach (array_keys($result) as $key) {
            echo "- {$key}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    echo "Error File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n✅ Debug complete!\n";
