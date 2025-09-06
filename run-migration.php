<?php

/**
 * Run Supabase Migration
 */

require_once 'vendor/autoload.php';

// Load Laravel configuration
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "🔄 Running Supabase Migration\n";
echo "============================\n\n";

try {
    // Check if columns already exist
    if (Schema::hasColumn('users', 'supabase_id')) {
        echo "✅ Supabase columns already exist!\n";
    } else {
        echo "Adding Supabase columns to users table...\n";
        
        Schema::table('users', function (Blueprint $table) {
            $table->string('supabase_id')->nullable()->unique()->after('id');
            $table->timestamp('admin_approved_at')->nullable()->after('email_verified_at');
            $table->timestamp('admin_rejected_at')->nullable()->after('admin_approved_at');
            $table->string('status')->default('pending')->after('role');
        });
        
        echo "✅ Migration completed successfully!\n";
    }
    
    // Verify columns exist
    echo "\nVerifying columns:\n";
    echo "- supabase_id: " . (Schema::hasColumn('users', 'supabase_id') ? '✅' : '❌') . "\n";
    echo "- admin_approved_at: " . (Schema::hasColumn('users', 'admin_approved_at') ? '✅' : '❌') . "\n";
    echo "- admin_rejected_at: " . (Schema::hasColumn('users', 'admin_rejected_at') ? '✅' : '❌') . "\n";
    echo "- status: " . (Schema::hasColumn('users', 'status') ? '✅' : '❌') . "\n";
    
} catch (\Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Error File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n✅ Migration script complete!\n";
