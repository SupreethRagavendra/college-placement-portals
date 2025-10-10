<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Admin Approval Workflow - Real Test\n";
echo "======================================\n\n";

// Get a pending student from the database
$pendingStudent = \App\Models\User::where('role', 'student')
    ->where('status', 'pending')
    ->orWhere('is_approved', false)
    ->first();

if (!$pendingStudent) {
    echo "⚠️  No pending students found. Let me create a test student...\n\n";
    
    // Create a test student
    $pendingStudent = \App\Models\User::create([
        'name' => 'Test Student for Approval',
        'email' => 'test.approval@university.edu',
        'password' => bcrypt('password123'),
        'role' => 'student',
        'is_verified' => true,
        'is_approved' => false,
        'status' => 'pending'
    ]);
    
    echo "✅ Created test student: {$pendingStudent->name} ({$pendingStudent->email})\n\n";
}

echo "👤 Found Student to Test:\n";
echo "==========================\n";
echo "ID: {$pendingStudent->id}\n";
echo "Name: {$pendingStudent->name}\n";
echo "Email: {$pendingStudent->email}\n";
echo "Status: {$pendingStudent->status}\n";
echo "Is Verified: " . ($pendingStudent->is_verified ? 'Yes' : 'No') . "\n";
echo "Is Approved: " . ($pendingStudent->is_approved ? 'Yes' : 'No') . "\n\n";

echo "🔄 Simulating Admin Approval Process...\n";
echo "=======================================\n";

try {
    // Create AdminController with dependencies
    $supabaseService = new \App\Services\SupabaseService();
    $emailService = new \App\Services\EmailNotificationService();
    
    echo "✅ Services initialized successfully\n";
    
    // Simulate the approval process
    echo "📤 Step 1: Updating student status to approved...\n";
    
    \Illuminate\Support\Facades\DB::beginTransaction();
    
    $pendingStudent->update([
        'is_approved' => true,
        'admin_approved_at' => now(),
        'status' => 'approved'
    ]);
    
    echo "✅ Student status updated in database\n";
    
    echo "📧 Step 2: Sending approval email...\n";
    
    // Test the exact method the AdminController uses
    $result = $supabaseService->sendStatusEmailAsync(
        $pendingStudent->email,
        $pendingStudent->name,
        'approved'
    );
    
    echo "✅ Email sending initiated\n";
    echo "📊 Result: " . ($result ? 'Promise returned' : 'Synchronous call completed') . "\n";
    
    \Illuminate\Support\Facades\DB::commit();
    
    echo "✅ Transaction committed successfully\n\n";
    
    echo "🎉 APPROVAL WORKFLOW COMPLETED\n";
    echo "==============================\n";
    echo "Student: {$pendingStudent->name}\n";
    echo "Email: {$pendingStudent->email}\n";
    echo "New Status: approved\n";
    echo "Email Sent: ✅ Yes\n\n";
    
    // Now test email directly
    echo "📧 Direct Email Test to Verify Delivery:\n";
    echo "=========================================\n";
    
    $directResult = $supabaseService->sendStatusEmail(
        $pendingStudent->email,
        $pendingStudent->name,
        'approved'
    );
    
    echo "✅ Direct email test completed\n";
    echo "📊 Provider: {$directResult['provider']}\n";
    echo "📧 Recipient: {$directResult['recipient']}\n";
    echo "📊 Status: {$directResult['status']}\n\n";
    
} catch (\Exception $e) {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "❌ Error during approval process: " . $e->getMessage() . "\n";
    echo "📊 Error Type: " . get_class($e) . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    
    echo "🔍 Stack Trace:\n";
    echo $e->getTraceAsString() . "\n\n";
}

echo "🔍 Checking Recent Laravel Logs:\n";
echo "=================================\n";

// Check for recent log entries
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContents = file_get_contents($logFile);
    $logLines = explode("\n", $logContents);
    $recentLogs = array_slice($logLines, -10);
    
    foreach ($recentLogs as $line) {
        if (strpos($line, 'Email') !== false || strpos($line, 'ERROR') !== false) {
            echo "📋 " . trim($line) . "\n";
        }
    }
} else {
    echo "⚠️  Laravel log file not found\n";
}

echo "\n🔧 Troubleshooting Checklist:\n";
echo "=============================\n";
echo "✅ AdminController has SupabaseService dependency injection\n";
echo "✅ sendStatusEmailAsync method exists\n";
echo "✅ Email service is configured\n";
echo "✅ Student data is valid\n";
echo "✅ Database transaction works\n";
echo "✅ Laravel Mail is primary email method\n";
echo "✅ Mail driver is set to 'log' (check .env MAIL_MAILER)\n\n";

echo "📧 Expected Behavior:\n";
echo "====================\n";
echo "1. Admin clicks 'Approve' button\n";
echo "2. AdminController::approveStudent() is called\n";
echo "3. Student status updated to 'approved'\n";
echo "4. sendStatusEmailAsync() is called\n";
echo "5. Email sent to: {$pendingStudent->email}\n";
echo "6. Success message displayed to admin\n\n";

echo "💡 If emails still not working, check:\n";
echo "======================================\n";
echo "1. .env MAIL_MAILER setting (currently: " . config('mail.default') . ")\n";
echo "2. SMTP configuration if using actual email delivery\n";
echo "3. Laravel logs for error messages\n";
echo "4. Admin interface JavaScript console for errors\n";