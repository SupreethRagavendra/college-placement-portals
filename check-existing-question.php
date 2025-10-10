<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== CHECKING EXISTING QUESTION STRUCTURE ===\n\n";
    
    // Get one existing question
    $question = DB::table('questions')->first();
    
    if ($question) {
        echo "Found existing question:\n";
        echo "ID: {$question->id}\n";
        echo "Question: " . substr($question->question, 0, 60) . "...\n";
        echo "Category ID: {$question->category_id}\n\n";
        
        echo "All fields in this question:\n";
        foreach ((array)$question as $field => $value) {
            if ($value === null) {
                echo "  {$field}: NULL\n";
            } else if (is_string($value) && strlen($value) > 50) {
                echo "  {$field}: " . substr($value, 0, 47) . "...\n";
            } else {
                echo "  {$field}: {$value}\n";
            }
        }
    } else {
        echo "No questions found in database.\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
