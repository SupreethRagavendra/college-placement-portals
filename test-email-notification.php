<?php

/**
 * Test script for Email Notification Functionality
 * 
 * This script tests the complete email notification workflow:
 * 1. Laravel SupabaseService integration
 * 2. Edge Function communication
 * 3. Email delivery
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\SupabaseService;
use Illuminate\Support\Facades\Config;

// Load environment configuration
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Setup Laravel-style config for testing
Config::set('supabase.url', $_ENV['SUPABASE_URL'] ?? '');
Config::set('supabase.anon_key', $_ENV['SUPABASE_ANON_KEY'] ?? '');
Config::set('supabase.service_role_key', $_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? '');
Config::set('app.name', $_ENV['APP_NAME'] ?? 'College Placement Portal');

echo "🧪 Email Notification Test Script\n";
echo "==================================\n\n";

// Initialize SupabaseService
try {
    $supabaseService = new SupabaseService();
    echo "✅ SupabaseService initialized successfully\n";
} catch (Exception $e) {
    echo "❌ Failed to initialize SupabaseService: " . $e->getMessage() . "\n";
    exit(1);
}

// Test configuration
$testData = [
    'student_email' => 'test@example.com',
    'student_name' => 'John Test Student',
    'status' => 'approved',
    'rejection_reason' => null
];

echo "📧 Test Configuration:\n";
echo "   Email: {$testData['student_email']}\n";
echo "   Name: {$testData['student_name']}\n";
echo "   Status: {$testData['status']}\n\n";

// Get user input for actual test
echo "Enter test details (or press Enter to use defaults):\n";

$email = trim(fgets(STDIN));
if (!empty($email)) {
    $testData['student_email'] = $email;
}

echo "Student name [{$testData['student_name']}]: ";
$name = trim(fgets(STDIN));
if (!empty($name)) {
    $testData['student_name'] = $name;
}

echo "Status (approved/rejected) [{$testData['status']}]: ";
$status = trim(fgets(STDIN));
if (!empty($status) && in_array($status, ['approved', 'rejected'])) {
    $testData['status'] = $status;
}

if ($testData['status'] === 'rejected') {
    echo "Rejection reason (optional): ";
    $reason = trim(fgets(STDIN));
    if (!empty($reason)) {
        $testData['rejection_reason'] = $reason;
    }
}

echo "\n🚀 Running Tests...\n";
echo "===================\n\n";

// Test 1: Synchronous email sending
echo "Test 1: Synchronous Email Sending\n";
echo "-----------------------------------\n";

try {
    $result = $supabaseService->sendStatusEmail(
        $testData['student_email'],
        $testData['student_name'],
        $testData['status'],
        $testData['rejection_reason']
    );
    
    echo "✅ Synchronous email sent successfully\n";
    echo "   Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "❌ Synchronous email failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Asynchronous email sending
echo "Test 2: Asynchronous Email Sending\n";
echo "------------------------------------\n";

try {
    $promise = $supabaseService->sendStatusEmailAsync(
        $testData['student_email'],
        $testData['student_name'],
        $testData['status'],
        $testData['rejection_reason']
    );
    
    if ($promise) {
        echo "✅ Asynchronous email initiated successfully\n";
        echo "   Promise created: " . get_class($promise) . "\n";
        
        // Wait for the promise to resolve (for testing purposes)
        try {
            $result = $promise->wait();
            echo "✅ Asynchronous email completed successfully\n";
            echo "   Response status: " . $result->getStatusCode() . "\n";
        } catch (Exception $e) {
            echo "❌ Asynchronous email failed during execution: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⚠️  Asynchronous email returned null (check logs for errors)\n";
    }
} catch (Exception $e) {
    echo "❌ Asynchronous email failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Direct Edge Function call
echo "Test 3: Direct Edge Function Call\n";
echo "----------------------------------\n";

try {
    $payload = [
        'student_email' => $testData['student_email'],
        'student_name' => $testData['student_name'],
        'status' => $testData['status'],
        'college_name' => Config::get('app.name')
    ];
    
    if ($testData['rejection_reason']) {
        $payload['rejection_reason'] = $testData['rejection_reason'];
    }
    
    $result = $supabaseService->callFunction('send-status-email', $payload, false);
    
    echo "✅ Direct Edge Function call successful\n";
    echo "   Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "❌ Direct Edge Function call failed: " . $e->getMessage() . "\n";
}

echo "\n🏁 Test Complete!\n";
echo "==================\n\n";

echo "📋 Summary:\n";
echo "   - Check your email inbox (and spam folder)\n";
echo "   - Monitor Supabase function logs: supabase functions logs send-status-email\n";
echo "   - Check Laravel logs for any issues\n\n";

echo "🔧 Troubleshooting:\n";
echo "   - Ensure SendGrid API key is set in Supabase secrets\n";
echo "   - Verify sender email is authenticated in SendGrid\n";
echo "   - Check that Edge Function is deployed properly\n";
echo "   - Confirm environment variables are set correctly\n\n";

echo "📚 For more help, see: EMAIL_NOTIFICATION_SETUP.md\n";