<?php

/**
 * Test Direct User Creation in Database
 */

require_once 'vendor/autoload.php';

// Load Laravel configuration
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "🧪 Testing Direct User Creation\n";
echo "==============================\n\n";

try {
    // Delete existing user first
    $existingUser = User::where('email', 'supreethvennila@gmail.com')->first();
    if ($existingUser) {
        echo "Deleting existing user...\n";
        $existingUser->delete();
    }
    
    // Create user directly
    $user = User::create([
        'name' => 'Supreeth Vennila',
        'email' => 'supreethvennila@gmail.com',
        'password' => Hash::make('TestPassword123!'),
        'role' => 'student',
        'supabase_id' => 'test-supabase-id-' . time(),
        'status' => 'pending',
    ]);
    
    echo "✅ User created successfully!\n";
    echo "ID: " . $user->id . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Role: " . $user->role . "\n";
    echo "Status: " . $user->status . "\n";
    echo "Supabase ID: " . $user->supabase_id . "\n";
    
} catch (\Exception $e) {
    echo "❌ User creation failed: " . $e->getMessage() . "\n";
    echo "Error File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n✅ Test complete!\n";
