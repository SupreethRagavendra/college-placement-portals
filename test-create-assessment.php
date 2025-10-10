<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Assessment;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;

try {
    echo "\n=== CREATING TEST ASSESSMENT ===\n\n";
    
    // Get or create admin user
    $admin = User::where('role', 'admin')->first();
    if (!$admin) {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'department' => 'Administration'
        ]);
        echo "Created admin user\n";
    }
    
    // First, check the questions table structure
    echo "\nChecking questions table structure...\n";
    $columns = DB::getSchemaBuilder()->getColumnListing('questions');
    echo "Columns in questions table: " . implode(', ', $columns) . "\n";
    
    // Create a test question
    echo "\nCreating test question...\n";
    
    $questionData = [
        'question' => 'Test Question',
        'question_text' => 'Test Question',
        'option_a' => 'Option A',
        'option_b' => 'Option B', 
        'option_c' => 'Option C',
        'option_d' => 'Option D',
        'correct_answer' => 'A',
        'marks' => 5,
        'time_per_question' => 60,
        'difficulty_level' => 'easy',
        'is_active' => true,
        'category' => 'Technical'
    ];
    
    echo "Attempting to create with data:\n";
    print_r($questionData);
    
    $question = Question::create($questionData);
    
    echo "\n✓ Successfully created question with ID: {$question->id}\n";
    
} catch (\Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
