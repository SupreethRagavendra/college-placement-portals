<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

try {
    echo "=== CREATING SAMPLE ASSESSMENTS ===\n\n";
    
    DB::beginTransaction();
    
    // Get or create admin user
    $admin = User::where('role', 'admin')->first();
    if (!$admin) {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'department' => 'Admin'
        ]);
        echo "Created admin user with ID: {$admin->id}\n";
    }
    
    // Get category IDs
    $technicalCategoryId = DB::table('categories')->where('name', 'Technical')->value('id');
    $aptitudeCategoryId = DB::table('categories')->where('name', 'Aptitude')->value('id');
    
    echo "Technical Category ID: {$technicalCategoryId}\n";
    echo "Aptitude Category ID: {$aptitudeCategoryId}\n\n";
    
    // Create Technical Assessment
    $techAssessmentId = DB::table('assessments')->insertGetId([
        'name' => 'Technical Assessment - Programming Fundamentals',
        'description' => 'This assessment tests your knowledge of programming concepts, data structures, and algorithms.',
        'category' => 'Technical',
        'time_limit' => 20,
        'total_time' => 20,
        'duration' => 20,
        'is_active' => true,
        'allow_multiple_attempts' => false,
        'pass_percentage' => 50,
        'status' => 'active',
        'created_by' => $admin->id,
        'show_results_immediately' => true,
        'show_correct_answers' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✓ Created Technical Assessment with ID: {$techAssessmentId}\n";
    
    // Create Aptitude Assessment
    $aptitudeAssessmentId = DB::table('assessments')->insertGetId([
        'name' => 'Aptitude Assessment - Logical Reasoning',
        'description' => 'This assessment evaluates your logical reasoning, analytical thinking, and problem-solving abilities.',
        'category' => 'Aptitude',
        'time_limit' => 15,
        'total_time' => 15,
        'duration' => 15,
        'is_active' => true,
        'allow_multiple_attempts' => false,
        'pass_percentage' => 50,
        'status' => 'active',
        'created_by' => $admin->id,
        'show_results_immediately' => true,
        'show_correct_answers' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✓ Created Aptitude Assessment with ID: {$aptitudeAssessmentId}\n\n";
    
    // Technical Questions
    $techQuestions = [
        [
            'question' => 'Which data structure uses LIFO (Last In First Out) principle?',
            'option_a' => 'Queue',
            'option_b' => 'Stack',
            'option_c' => 'Array',
            'option_d' => 'Linked List',
            'correct_answer' => 'B',
            'explanation' => 'Stack follows LIFO principle where the last element added is the first one to be removed.'
        ],
        [
            'question' => 'What is the time complexity of binary search in a sorted array?',
            'option_a' => 'O(n)',
            'option_b' => 'O(n²)',
            'option_c' => 'O(log n)',
            'option_d' => 'O(1)',
            'correct_answer' => 'C',
            'explanation' => 'Binary search divides the search space in half at each step, resulting in O(log n) time complexity.'
        ],
        [
            'question' => 'Which programming paradigm does JavaScript primarily support?',
            'option_a' => 'Object-Oriented Programming only',
            'option_b' => 'Functional Programming only',
            'option_c' => 'Procedural Programming only',
            'option_d' => 'Multi-paradigm (OOP, Functional, and Procedural)',
            'correct_answer' => 'D',
            'explanation' => 'JavaScript is a multi-paradigm language supporting object-oriented, functional, and procedural programming styles.'
        ],
        [
            'question' => 'What does SQL stand for?',
            'option_a' => 'Structured Query Language',
            'option_b' => 'Simple Question Language',
            'option_c' => 'Sequential Query Language',
            'option_d' => 'System Query Language',
            'correct_answer' => 'A',
            'explanation' => 'SQL stands for Structured Query Language, used for managing relational databases.'
        ]
    ];
    
    // Insert Technical Questions
    echo "Creating Technical Questions:\n";
    foreach ($techQuestions as $index => $q) {
        $correctIndex = ord($q['correct_answer']) - ord('A');
        $options = [
            0 => $q['option_a'],
            1 => $q['option_b'],
            2 => $q['option_c'],
            3 => $q['option_d']
        ];
        
        $questionId = DB::table('questions')->insertGetId([
            'question' => $q['question'],
            'category_id' => $technicalCategoryId,
            'options' => json_encode($options),
            'option_a' => $q['option_a'],
            'option_b' => $q['option_b'],
            'option_c' => $q['option_c'],
            'option_d' => $q['option_d'],
            'correct_option' => $correctIndex,
            'correct_answer' => $q['correct_answer'],
            'marks' => 5,
            'time_per_question' => 60,
            'difficulty_level' => 'medium',
            'explanation' => $q['explanation'],
            'is_active' => true,
            'question_type' => 'mcq',
            'order' => $index + 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Link to assessment
        DB::table('assessment_questions')->insert([
            'assessment_id' => $techAssessmentId,
            'question_id' => $questionId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "  ✓ Question " . ($index + 1) . ": ID {$questionId}\n";
    }
    
    // Aptitude Questions
    $aptitudeQuestions = [
        [
            'question' => 'If all roses are flowers and some flowers fade quickly, which statement must be true?',
            'option_a' => 'All roses fade quickly',
            'option_b' => 'Some roses fade quickly',
            'option_c' => 'No roses fade quickly',
            'option_d' => 'Cannot be determined from given information',
            'correct_answer' => 'D',
            'explanation' => 'We know roses are flowers, but we only know SOME flowers fade quickly, not which ones. So we cannot determine if roses fade quickly.'
        ],
        [
            'question' => 'What is the next number in the sequence: 2, 6, 12, 20, 30, ?',
            'option_a' => '40',
            'option_b' => '42',
            'option_c' => '44',
            'option_d' => '36',
            'correct_answer' => 'B',
            'explanation' => 'The pattern is n×(n+1): 1×2=2, 2×3=6, 3×4=12, 4×5=20, 5×6=30, 6×7=42'
        ],
        [
            'question' => 'If A is coded as 1, B as 2, C as 3, what is the code for FACE?',
            'option_a' => '6134',
            'option_b' => '6135',
            'option_c' => '6133',
            'option_d' => '6132',
            'correct_answer' => 'B',
            'explanation' => 'F=6, A=1, C=3, E=5. So FACE = 6135'
        ],
        [
            'question' => 'A train traveling at 60 km/h crosses a platform in 30 seconds. If the platform is 200 meters long and the train is 100 meters long, what is the total distance covered?',
            'option_a' => '300 meters',
            'option_b' => '500 meters',
            'option_c' => '200 meters',
            'option_d' => '100 meters',
            'correct_answer' => 'A',
            'explanation' => 'To completely cross the platform, the train travels its own length (100m) plus the platform length (200m) = 300 meters total.'
        ]
    ];
    
    // Insert Aptitude Questions
    echo "\nCreating Aptitude Questions:\n";
    foreach ($aptitudeQuestions as $index => $q) {
        $correctIndex = ord($q['correct_answer']) - ord('A');
        $options = [
            0 => $q['option_a'],
            1 => $q['option_b'],
            2 => $q['option_c'],
            3 => $q['option_d']
        ];
        
        $questionId = DB::table('questions')->insertGetId([
            'question' => $q['question'],
            'category_id' => $aptitudeCategoryId,
            'options' => json_encode($options),
            'option_a' => $q['option_a'],
            'option_b' => $q['option_b'],
            'option_c' => $q['option_c'],
            'option_d' => $q['option_d'],
            'correct_option' => $correctIndex,
            'correct_answer' => $q['correct_answer'],
            'marks' => 5,
            'time_per_question' => 90,
            'difficulty_level' => 'medium',
            'explanation' => $q['explanation'],
            'is_active' => true,
            'question_type' => 'mcq',
            'order' => $index + 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Link to assessment
        DB::table('assessment_questions')->insert([
            'assessment_id' => $aptitudeAssessmentId,
            'question_id' => $questionId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "  ✓ Question " . ($index + 1) . ": ID {$questionId}\n";
    }
    
    DB::commit();
    
    echo "\n=== SUCCESS! ===\n";
    echo "✅ Created 2 assessments with 8 questions total\n";
    echo "\n📋 Summary:\n";
    echo "1. Technical Assessment (ID: {$techAssessmentId}) - 4 questions, 20 minutes\n";
    echo "2. Aptitude Assessment (ID: {$aptitudeAssessmentId}) - 4 questions, 15 minutes\n";
    echo "\n🎯 Both assessments are active and ready for students!\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
