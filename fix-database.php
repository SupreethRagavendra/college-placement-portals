<?php

/**
 * Fix Database Schema for Supabase Integration
 */

require_once 'vendor/autoload.php';

// Load Laravel configuration
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🔧 Fixing Database Schema\n";
echo "========================\n\n";

try {
    // Check current database connection
    echo "Database: " . config('database.default') . "\n";
    echo "Host: " . config('database.connections.pgsql.host') . "\n\n";
    
    // Check if users table exists and get its structure
    $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position");
    echo "Current users table columns:\n";
    foreach ($columns as $column) {
        echo "- " . $column->column_name . "\n";
    }
    echo "\n";
    
    // Check if supabase_id column exists
    $hasSupabaseId = false;
    foreach ($columns as $column) {
        if ($column->column_name === 'supabase_id') {
            $hasSupabaseId = true;
            break;
        }
    }
    
    if (!$hasSupabaseId) {
        echo "Adding missing Supabase columns...\n";
        
        // Add supabase_id column
        DB::statement("ALTER TABLE users ADD COLUMN supabase_id VARCHAR(255) UNIQUE");
        echo "✅ Added supabase_id column\n";
        
        // Add admin_approved_at column
        DB::statement("ALTER TABLE users ADD COLUMN admin_approved_at TIMESTAMP NULL");
        echo "✅ Added admin_approved_at column\n";
        
        // Add admin_rejected_at column
        DB::statement("ALTER TABLE users ADD COLUMN admin_rejected_at TIMESTAMP NULL");
        echo "✅ Added admin_rejected_at column\n";
        
        // Add status column
        DB::statement("ALTER TABLE users ADD COLUMN status VARCHAR(255) DEFAULT 'pending'");
        echo "✅ Added status column\n";
        
        echo "\n✅ All Supabase columns added successfully!\n";
    } else {
        echo "✅ Supabase columns already exist!\n";
    }
    
    // Verify the columns exist
    echo "\nVerifying columns:\n";
    $newColumns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position");
    foreach ($newColumns as $column) {
        echo "- " . $column->column_name . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Error File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n✅ Database fix complete!\n";
