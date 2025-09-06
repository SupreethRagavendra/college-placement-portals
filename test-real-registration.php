<?php

/**
 * Test Real Registration with Valid Email
 */

require_once 'vendor/autoload.php';

use App\Services\SupabaseService;

// Load Laravel configuration
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Real Registration Test\n";
echo "========================\n\n";

try {
    $supabaseService = new SupabaseService();
    
    // Use a valid email format (Gmail format)
    $testEmail = 'testuser' . time() . '@gmail.com';
    $testPassword = 'TestPassword123!';
    
    echo "Testing registration with:\n";
    echo "Email: {$testEmail}\n";
    echo "Password: {$testPassword}\n\n";
    
    $result = $supabaseService->signUp($testEmail, $testPassword, [
        'name' => 'Test User',
        'role' => 'student'
    ]);
    
    echo "✅ Registration successful!\n";
    echo "User ID: " . ($result['user']['id'] ?? 'N/A') . "\n";
    echo "Email: " . ($result['user']['email'] ?? 'N/A') . "\n";
    echo "Email Confirmed: " . ($result['user']['email_confirmed_at'] ? 'Yes' : 'No') . "\n";
    echo "Created At: " . ($result['user']['created_at'] ?? 'N/A') . "\n\n";
    
    echo "🎉 Registration is working correctly!\n";
    echo "The issue was likely with the email format you used.\n\n";
    
    echo "📧 Valid Email Formats:\n";
    echo "- Gmail: user@gmail.com\n";
    echo "- Yahoo: user@yahoo.com\n";
    echo "- Outlook: user@outlook.com\n";
    echo "- Custom: user@yourdomain.com\n\n";
    
    echo "❌ Invalid Email Formats:\n";
    echo "- @example.com (blocked by Supabase)\n";
    echo "- @test.com (might be blocked)\n";
    echo "- Invalid formats: user@, @domain.com, etc.\n\n";
    
} catch (\Exception $e) {
    echo "❌ Registration failed: " . $e->getMessage() . "\n\n";
    
    if (str_contains($e->getMessage(), 'email_address_invalid')) {
        echo "💡 This error means the email format is invalid.\n";
        echo "Try using a real email address like:\n";
        echo "- yourname@gmail.com\n";
        echo "- yourname@yahoo.com\n";
        echo "- yourname@outlook.com\n\n";
    } elseif (str_contains($e->getMessage(), 'User already registered')) {
        echo "💡 This email is already registered.\n";
        echo "Try using a different email address.\n\n";
    }
}

echo "✅ Test complete!\n";
