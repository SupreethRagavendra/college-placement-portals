<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== CHECKING DATABASE SCHEMA ===\n\n";
    
    // Check questions table constraints
    $result = DB::select("
        SELECT column_name, is_nullable, column_default, data_type 
        FROM information_schema.columns 
        WHERE table_name = 'questions' 
        AND table_schema = 'public'
        ORDER BY ordinal_position
    ");
    
    echo "Questions table schema:\n";
    foreach ($result as $column) {
        echo "- {$column->column_name}: {$column->data_type}, nullable: {$column->is_nullable}, default: " . ($column->column_default ?? 'NULL') . "\n";
    }
    
    echo "\nCategories available:\n";
    $categories = DB::table('categories')->get();
    foreach ($categories as $cat) {
        echo "- ID: {$cat->id}, Name: {$cat->name}\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
