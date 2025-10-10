<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Question;

try {
    echo "=== CREATING FINAL ASSESSMENTS ===\n\n";
    
    DB::beginTransaction();
    
    // Get admin user
    $admin = User::where('role', 'admin')->first();
    if (!$admin) {
        $admin = User::first(); // Use any user as admin
    }
    
    // Get category IDs
    $technicalCategoryId = DB::table('categories')->where('name', 'Technical')->value('id');
    $aptitudeCategoryId = DB::table('categories')->where('name', 'Aptitude')->value('id');
    
    echo "Using Admin ID: {$admin->id}\n";
    echo "Technical Category ID: {$technicalCategoryId}\n";
    echo "Aptitude Category ID: {$aptitudeCategoryId}\n\n";
    
    // Create Technical Assessment
    $techAssessmentId = DB::table('assessments')->insertGetId([
        'name' => 'Technical Assessment - Programming Fundamentals',
        'description' => 'Test your programming knowledge with data structures and algorithms',
        'category' => 'Technical',
        'time_limit' => 20,
        'is_active' => true,
        'created_by' => $admin->id,
        'status' => 'active',
        'pass_percentage' => 50,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Created Technical Assessment (ID: {$techAssessmentId})\n";
    
    // Create Aptitude Assessment  
    $aptitudeAssessmentId = DB::table('assessments')->insertGetId([
        'name' => 'Aptitude Assessment - Logical Reasoning',
        'description' => 'Test your logical reasoning and analytical thinking skills',
        'category' => 'Aptitude', 
        'time_limit' => 15,
        'is_active' => true,
        'created_by' => $admin->id,
        'status' => 'active',
        'pass_percentage' => 50,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Created Aptitude Assessment (ID: {$aptitudeAssessmentId})\n\n";
    
    // Technical Questions
    $techQuestions = [
        [
            'question' => 'Which data structure uses LIFO (Last In First Out) principle?',
            'option_a' => 'Queue',
            'option_b' => 'Stack', 
            'option_c' => 'Array',
            'option_d' => 'Linked List',
            'correct_answer' => 'B'
        ],
        [
            'question' => 'What is the time complexity of binary search in a sorted array?',
            'option_a' => 'O(n)',
            'option_b' => 'O(n²)',
            'option_c' => 'O(log n)',
            'option_d' => 'O(1)',
            'correct_answer' => 'C'
        ],
        [
            'question' => 'Which programming paradigm does JavaScript primarily support?',
            'option_a' => 'Object-Oriented only',
            'option_b' => 'Functional only', 
            'option_c' => 'Procedural only',
            'option_d' => 'Multi-paradigm',
            'correct_answer' => 'D'
        ],
        [
            'question' => 'What does SQL stand for?',
            'option_a' => 'Structured Query Language',
            'option_b' => 'Simple Question Language',
            'option_c' => 'Sequential Query Language', 
            'option_d' => 'System Query Language',
            'correct_answer' => 'A'
        ]
    ];
    
    echo "Creating Technical Questions:\n";
    foreach ($techQuestions as $index => $q) {
        $correctIndex = ord($q['correct_answer']) - ord('A');
        
        $question = Question::create([
            'question' => $q['question'],
            'category_id' => $technicalCategoryId,
            'option_a' => $q['option_a'],
            'option_b' => $q['option_b'],
            'option_c' => $q['option_c'],
            'option_d' => $q['option_d'],
            'correct_answer' => $q['correct_answer'],
            'correct_option' => $correctIndex,
            'is_active' => true,
            'question_type' => 'mcq'
        ]);
        
        // Link to assessment
        DB::table('assessment_questions')->insert([
            'assessment_id' => $techAssessmentId,
            'question_id' => $question->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "  ✅ Question " . ($index + 1) . " (ID: {$question->id})\n";
    }
    
    // Aptitude Questions
    $aptitudeQuestions = [
        [
            'question' => 'If all roses are flowers and some flowers fade quickly, which statement must be true?',
            'option_a' => 'All roses fade quickly',
            'option_b' => 'Some roses fade quickly',
            'option_c' => 'No roses fade quickly',
            'option_d' => 'Cannot be determined',
            'correct_answer' => 'D'
        ],
        [
            'question' => 'What is the next number in the sequence: 2, 6, 12, 20, 30, ?',
            'option_a' => '40',
            'option_b' => '42',
            'option_c' => '44',
            'option_d' => '36',
            'correct_answer' => 'B'
        ],
        [
            'question' => 'If A is coded as 1, B as 2, C as 3, what is the code for FACE?',
            'option_a' => '6134',
            'option_b' => '6135',
            'option_c' => '6133',
            'option_d' => '6132',
            'correct_answer' => 'B'
        ],
        [
            'question' => 'A train crosses a 200m platform in 30 seconds. If train length is 100m, total distance covered?',
            'option_a' => '300 meters',
            'option_b' => '500 meters',
            'option_c' => '200 meters',
            'option_d' => '100 meters',
            'correct_answer' => 'A'
        ]
    ];
    
    echo "\nCreating Aptitude Questions:\n";
    foreach ($aptitudeQuestions as $index => $q) {
        $correctIndex = ord($q['correct_answer']) - ord('A');
        
        $question = Question::create([
            'question' => $q['question'],
            'category_id' => $aptitudeCategoryId,
            'option_a' => $q['option_a'],
            'option_b' => $q['option_b'],
            'option_c' => $q['option_c'],
            'option_d' => $q['option_d'],
            'correct_answer' => $q['correct_answer'],
            'correct_option' => $correctIndex,
            'is_active' => true,
            'question_type' => 'mcq'
        ]);
        
        // Link to assessment
        DB::table('assessment_questions')->insert([
            'assessment_id' => $aptitudeAssessmentId,
            'question_id' => $question->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "  ✅ Question " . ($index + 1) . " (ID: {$question->id})\n";
    }
    
    DB::commit();
    
    echo "\n🎉 SUCCESS! Created 2 assessments with 8 questions total\n\n";
    echo "📋 SUMMARY:\n";
    echo "1. Technical Assessment (ID: {$techAssessmentId})\n";
    echo "   - Category: Technical\n";
    echo "   - Duration: 20 minutes\n";
    echo "   - Questions: 4\n";
    echo "   - Status: Active\n\n";
    echo "2. Aptitude Assessment (ID: {$aptitudeAssessmentId})\n";
    echo "   - Category: Aptitude\n";
    echo "   - Duration: 15 minutes\n";
    echo "   - Questions: 4\n";
    echo "   - Status: Active\n\n";
    echo "🚀 Both assessments are ready for students to take!\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
