<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Question;

try {
    echo "=== TESTING MINIMAL QUESTION CREATION ===\n";
    
    $technicalCategoryId = DB::table('categories')->where('name', 'Technical')->value('id');
    echo "Category ID: {$technicalCategoryId}\n";
    
    // Try with absolute minimal fields
    echo "\nAttempting to create question with minimal fields...\n";
    
    $questionData = [
        'question' => 'Test question?',
        'category_id' => $technicalCategoryId,
        'is_active' => true
    ];
    
    echo "Data: " . json_encode($questionData, JSON_PRETTY_PRINT) . "\n";
    
    $question = Question::create($questionData);
    
    echo "✅ Success! Question ID: {$question->id}\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    
    // Show more details
    if (method_exists($e, 'errorInfo')) {
        echo "Error Info: " . json_encode($e->errorInfo) . "\n";
    }
}
