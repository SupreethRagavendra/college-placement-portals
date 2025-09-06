<?php

/**
 * Test Registration with supreethvennila@gmail.com
 */

require_once 'vendor/autoload.php';

use App\Services\SupabaseService;

// Load Laravel configuration
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Testing Registration with supreethvennila@gmail.com\n";
echo "====================================================\n\n";

try {
    $supabaseService = new SupabaseService();
    
    // Test with the provided email
    $testEmail = 'supreethvennila@gmail.com';
    $testPassword = 'TestPassword123!';
    
    echo "Testing registration with:\n";
    echo "Email: {$testEmail}\n";
    echo "Password: {$testPassword}\n\n";
    
    $result = $supabaseService->signUp($testEmail, $testPassword, [
        'name' => 'Supreeth Vennila',
        'role' => 'student'
    ]);
    
    echo "✅ Registration successful!\n";
    echo "User ID: " . ($result['id'] ?? 'N/A') . "\n";
    echo "Email: " . ($result['email'] ?? 'N/A') . "\n";
    echo "Email Confirmed: " . (isset($result['email_confirmed_at']) && $result['email_confirmed_at'] ? 'Yes' : 'No') . "\n";
    echo "Confirmation Sent At: " . ($result['confirmation_sent_at'] ?? 'N/A') . "\n";
    echo "Created At: " . ($result['created_at'] ?? 'N/A') . "\n\n";
    
    echo "📧 Check your email for verification link!\n";
    echo "After email verification, the account will be pending admin approval.\n\n";
    
} catch (\Exception $e) {
    echo "❌ Registration failed: " . $e->getMessage() . "\n\n";
    
    if (str_contains($e->getMessage(), 'email_address_invalid')) {
        echo "💡 This error means the email format is invalid.\n";
    } elseif (str_contains($e->getMessage(), 'User already registered')) {
        echo "💡 This email is already registered.\n";
        echo "Let's try to check if the user exists and their status.\n\n";
        
        // Try to get user info
        try {
            $loginResult = $supabaseService->signIn($testEmail, $testPassword);
            if (isset($loginResult['access_token'])) {
                $user = $supabaseService->getUser($loginResult['access_token']);
                echo "✅ User exists and can login!\n";
                echo "Email Confirmed: " . ($user['email_confirmed_at'] ? 'Yes' : 'No') . "\n";
                echo "User ID: " . ($user['id'] ?? 'N/A') . "\n";
            }
        } catch (\Exception $loginError) {
            echo "❌ Login also failed: " . $loginError->getMessage() . "\n";
        }
    }
}

echo "✅ Test complete!\n";
