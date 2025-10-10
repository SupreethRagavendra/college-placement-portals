<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== CHECKING REQUIRED COLUMNS ===\n\n";
    
    // Get detailed column information from PostgreSQL
    $columns = DB::select("
        SELECT 
            column_name, 
            is_nullable, 
            column_default, 
            data_type,
            character_maximum_length
        FROM information_schema.columns 
        WHERE table_name = 'questions' 
        AND table_schema = 'public'
        ORDER BY ordinal_position
    ");
    
    echo "Questions table columns:\n";
    echo str_repeat("=", 80) . "\n";
    printf("%-25s %-12s %-20s %-15s\n", "Column", "Nullable", "Default", "Type");
    echo str_repeat("=", 80) . "\n";
    
    $requiredColumns = [];
    foreach ($columns as $col) {
        printf("%-25s %-12s %-20s %-15s\n", 
            $col->column_name, 
            $col->is_nullable,
            $col->column_default ?? 'NULL',
            $col->data_type
        );
        
        if ($col->is_nullable === 'NO' && $col->column_default === null) {
            $requiredColumns[] = $col->column_name;
        }
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "⚠️  REQUIRED COLUMNS (NOT NULL, no default):\n";
    foreach ($requiredColumns as $col) {
        echo "  - {$col}\n";
    }
    
    echo "\n💡 These columns MUST have values when inserting questions.\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
