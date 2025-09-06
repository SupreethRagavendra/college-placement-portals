<?php

/**
 * Test Full Registration Process through Laravel Controller
 */

require_once 'vendor/autoload.php';

// Load Laravel configuration
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\SupabaseAuthController;
use App\Services\SupabaseService;
use App\Models\User;

echo "🧪 Testing Full Registration Process\n";
echo "====================================\n\n";

try {
    // Create controller instance
    $supabaseService = new SupabaseService();
    $controller = new SupabaseAuthController($supabaseService);
    
    // Create a mock request
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'name' => 'Supreeth Vennila',
        'email' => 'supreethvennila@gmail.com',
        'password' => 'TestPassword123!',
        'password_confirmation' => 'TestPassword123!',
        'role' => 'student'
    ]);
    
    echo "Testing registration with:\n";
    echo "Name: " . $request->name . "\n";
    echo "Email: " . $request->email . "\n";
    echo "Role: " . $request->role . "\n\n";
    
    // Check if user already exists
    $existingUser = User::where('email', $request->email)->first();
    if ($existingUser) {
        echo "⚠️  User already exists in database!\n";
        echo "Deleting existing user for fresh test...\n";
        $existingUser->delete();
    }
    
    // Call the registration method
    $response = $controller->register($request);
    
    echo "✅ Registration completed!\n";
    echo "Response type: " . get_class($response) . "\n";
    
    // Check if user was created in database
    $user = User::where('email', $request->email)->first();
    if ($user) {
        echo "\n✅ User created in local database!\n";
        echo "Name: " . $user->name . "\n";
        echo "Email: " . $user->email . "\n";
        echo "Role: " . $user->role . "\n";
        echo "Status: " . $user->status . "\n";
        echo "Supabase ID: " . $user->supabase_id . "\n";
        echo "Email Verified At: " . ($user->email_verified_at ? $user->email_verified_at : 'Not verified') . "\n";
        echo "Admin Approved At: " . ($user->admin_approved_at ? $user->admin_approved_at : 'Not approved') . "\n";
        echo "\nUser Status Check:\n";
        echo "- Is Student: " . ($user->isStudent() ? 'Yes' : 'No') . "\n";
        echo "- Is Pending Approval: " . ($user->isPendingApproval() ? 'Yes' : 'No') . "\n";
        echo "- Is Approved: " . ($user->isApproved() ? 'Yes' : 'No') . "\n";
    } else {
        echo "❌ User not created in local database!\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Registration failed: " . $e->getMessage() . "\n";
    echo "Error File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n✅ Test complete!\n";
