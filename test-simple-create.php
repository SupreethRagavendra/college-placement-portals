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
    echo "=== CREATING SIMPLE TEST ===\n";
    
    // Get or create admin user
    $admin = User::where('role', 'admin')->first();
    if (!$admin) {
        echo "Creating admin user...\n";
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'department' => 'Admin'
        ]);
    }
    echo "Admin ID: {$admin->id}\n";
    
    // Try creating assessment with minimal data
    echo "\nCreating assessment...\n";
    $assessment = Assessment::create([
        'name' => 'Test Assessment',
        'description' => 'Test Description',
        'category' => 'Technical',
        'time_limit' => 20,
        'is_active' => true,
        'created_by' => $admin->id
    ]);
    
    echo "✓ Assessment created with ID: {$assessment->id}\n";
    
    // Get category ID
    $categoryId = DB::table('categories')->where('name', 'Technical')->value('id');
    echo "Category ID: {$categoryId}\n";
    
    // Try creating question
    echo "\nCreating question...\n";
    $question = Question::create([
        'question' => 'What is 2+2?',
        'category_id' => $categoryId,
        'option_a' => '3',
        'option_b' => '4',
        'option_c' => '5',
        'option_d' => '6',
        'correct_answer' => 'B',
        'correct_option' => 1, // B is index 1
        'marks' => 1,
        'time_per_question' => 60,
        'difficulty_level' => 'easy',
        'is_active' => true,
        'question_type' => 'mcq',
        'order' => 1
    ]);
    
    echo "✓ Question created with ID: {$question->id}\n";
    
    // Link them
    DB::table('assessment_questions')->insert([
        'assessment_id' => $assessment->id,
        'question_id' => $question->id,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✓ Linked assessment and question\n";
    echo "\n=== SUCCESS ===\n";
    
} catch (\Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
