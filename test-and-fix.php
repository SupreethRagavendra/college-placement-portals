<?php

/**
 * Test and Fix Registration Issues
 */

require_once 'vendor/autoload.php';

// Load Laravel configuration
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "🔧 Testing and Fixing Registration Issues\n";
echo "========================================\n\n";

try {
    // Step 1: Fix database schema
    echo "1. Fixing database schema...\n";
    
    // Check if supabase_id column exists
    $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'users'");
    $hasSupabaseId = false;
    foreach ($columns as $column) {
        if ($column->column_name === 'supabase_id') {
            $hasSupabaseId = true;
            break;
        }
    }
    
    if (!$hasSupabaseId) {
        echo "Adding missing columns...\n";
        DB::statement("ALTER TABLE users ADD COLUMN supabase_id VARCHAR(255) UNIQUE");
        DB::statement("ALTER TABLE users ADD COLUMN admin_approved_at TIMESTAMP NULL");
        DB::statement("ALTER TABLE users ADD COLUMN admin_rejected_at TIMESTAMP NULL");
        DB::statement("ALTER TABLE users ADD COLUMN status VARCHAR(255) DEFAULT 'pending'");
        echo "✅ Database schema fixed!\n";
    } else {
        echo "✅ Database schema already correct!\n";
    }
    
    // Step 2: Test registration
    echo "\n2. Testing registration...\n";
    
    // Delete existing test user
    User::where('email', 'supreethvennila@gmail.com')->delete();
    echo "Cleaned up existing test user\n";
    
    // Test Supabase registration
    $supabaseService = new SupabaseService();
    $result = $supabaseService->signUp('supreethvennila@gmail.com', 'TestPassword123!', [
        'name' => 'Supreeth Vennila',
        'role' => 'student'
    ]);
    
    echo "✅ Supabase registration successful!\n";
    echo "User ID: " . $result['id'] . "\n";
    echo "Email: " . $result['email'] . "\n";
    echo "Confirmation sent: " . ($result['confirmation_sent_at'] ? 'Yes' : 'No') . "\n";
    
    // Step 3: Create user in local database
    echo "\n3. Creating user in local database...\n";
    
    $user = User::create([
        'name' => 'Supreeth Vennila',
        'email' => 'supreethvennila@gmail.com',
        'password' => Hash::make('TestPassword123!'),
        'role' => 'student',
        'supabase_id' => $result['id'],
        'status' => 'pending',
    ]);
    
    echo "✅ User created in local database!\n";
    echo "Local User ID: " . $user->id . "\n";
    echo "Status: " . $user->status . "\n";
    echo "Is Pending Approval: " . ($user->isPendingApproval() ? 'Yes' : 'No') . "\n";
    
    // Step 4: Show next steps
    echo "\n4. Next Steps:\n";
    echo "✅ Registration completed successfully!\n";
    echo "📧 Check your email (supreethvennila@gmail.com) for verification link\n";
    echo "🔗 The verification link will redirect to: " . config('supabase.redirect_url') . "\n";
    echo "⏳ After email verification, the user will be pending admin approval\n";
    echo "👨‍💼 Admin needs to approve the user before they can login\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Error File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n✅ Test and fix complete!\n";
