<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== CREATING WORKING ASSESSMENTS ===\n\n";
    
    DB::beginTransaction();
    
    // Get required data
    $admin = DB::table('users')->first();
    $technicalCategoryId = DB::table('categories')->where('name', 'Technical')->value('id');
    $aptitudeCategoryId = DB::table('categories')->where('name', 'Aptitude')->value('id');
    
    echo "Admin ID: {$admin->id}\n";
    echo "Technical Category: {$technicalCategoryId}\n";
    echo "Aptitude Category: {$aptitudeCategoryId}\n\n";
    
    // Clean up any existing test data
    DB::table('assessment_questions')->whereIn('assessment_id', function($query) {
        $query->select('id')->from('assessments')->where('name', 'LIKE', '%Test%');
    })->delete();
    
    DB::table('assessments')->where('name', 'LIKE', '%Test%')->delete();
    
    // Create Technical Assessment with all required fields
    $techAssessmentId = DB::table('assessments')->insertGetId([
        'name' => 'Technical Assessment - Programming Fundamentals',
        'description' => 'Test your programming knowledge with data structures and algorithms',
        'category' => 'Technical',
        'time_limit' => 20,
        'total_time' => 20,
        'duration' => 20,
        'is_active' => true,
        'created_by' => $admin->id,
        'status' => 'active',
        'pass_percentage' => 50,
        'total_marks' => 20,
        'allow_multiple_attempts' => false,
        'show_results_immediately' => true,
        'show_correct_answers' => true,
        'difficulty_level' => 'medium',
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
        'total_time' => 15,
        'duration' => 15,
        'is_active' => true,
        'created_by' => $admin->id,
        'status' => 'active',
        'pass_percentage' => 50,
        'total_marks' => 20,
        'allow_multiple_attempts' => false,
        'show_results_immediately' => true,
        'show_correct_answers' => true,
        'difficulty_level' => 'medium',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Created Aptitude Assessment (ID: {$aptitudeAssessmentId})\n\n";
    
    // Create questions with all required fields
    echo "Creating Technical Questions:\n";
    
    // Technical Questions
    $techQuestions = [
        [
            'question' => 'Which data structure uses LIFO (Last In First Out) principle?',
            'option_a' => 'Queue',
            'option_b' => 'Stack',
            'option_c' => 'Array',
            'option_d' => 'Linked List',
            'correct_answer' => 'B',
            'correct_option' => 1
        ],
        [
            'question' => 'What is the time complexity of binary search in a sorted array?',
            'option_a' => 'O(n)',
            'option_b' => 'O(n²)',
            'option_c' => 'O(log n)',
            'option_d' => 'O(1)',
            'correct_answer' => 'C',
            'correct_option' => 2
        ],
        [
            'question' => 'Which programming paradigm does JavaScript primarily support?',
            'option_a' => 'Object-Oriented only',
            'option_b' => 'Functional only',
            'option_c' => 'Procedural only',
            'option_d' => 'Multi-paradigm',
            'correct_answer' => 'D',
            'correct_option' => 3
        ],
        [
            'question' => 'What does SQL stand for?',
            'option_a' => 'Structured Query Language',
            'option_b' => 'Simple Question Language',
            'option_c' => 'Sequential Query Language',
            'option_d' => 'System Query Language',
            'correct_answer' => 'A',
            'correct_option' => 0
        ]
    ];
    
    foreach ($techQuestions as $index => $q) {
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
            'correct_answer' => $q['correct_answer'],
            'correct_option' => $q['correct_option'],
            'is_active' => true,
            'question_type' => 'mcq',
            'marks' => 5,
            'time_per_question' => 60,
            'difficulty_level' => 'medium',
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
        
        echo "  ✅ Question " . ($index + 1) . " (ID: {$questionId})\n";
    }
    
    echo "\nCreating Aptitude Questions:\n";
    
    // Aptitude Questions
    $aptitudeQuestions = [
        [
            'question' => 'If all roses are flowers and some flowers fade quickly, which statement must be true?',
            'option_a' => 'All roses fade quickly',
            'option_b' => 'Some roses fade quickly',
            'option_c' => 'No roses fade quickly',
            'option_d' => 'Cannot be determined',
            'correct_answer' => 'D',
            'correct_option' => 3
        ],
        [
            'question' => 'What is the next number in the sequence: 2, 6, 12, 20, 30, ?',
            'option_a' => '40',
            'option_b' => '42',
            'option_c' => '44',
            'option_d' => '36',
            'correct_answer' => 'B',
            'correct_option' => 1
        ],
        [
            'question' => 'If A is coded as 1, B as 2, C as 3, what is the code for FACE?',
            'option_a' => '6134',
            'option_b' => '6135',
            'option_c' => '6133',
            'option_d' => '6132',
            'correct_answer' => 'B',
            'correct_option' => 1
        ],
        [
            'question' => 'A train crosses a 200m platform in 30 seconds. If train length is 100m, total distance covered?',
            'option_a' => '300 meters',
            'option_b' => '500 meters',
            'option_c' => '200 meters',
            'option_d' => '100 meters',
            'correct_answer' => 'A',
            'correct_option' => 0
        ]
    ];
    
    foreach ($aptitudeQuestions as $index => $q) {
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
            'correct_answer' => $q['correct_answer'],
            'correct_option' => $q['correct_option'],
            'is_active' => true,
            'question_type' => 'mcq',
            'marks' => 5,
            'time_per_question' => 90,
            'difficulty_level' => 'medium',
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
        
        echo "  ✅ Question " . ($index + 1) . " (ID: {$questionId})\n";
    }
    
    DB::commit();
    
    // Final verification
    $techCount = DB::table('assessment_questions')->where('assessment_id', $techAssessmentId)->count();
    $aptitudeCount = DB::table('assessment_questions')->where('assessment_id', $aptitudeAssessmentId)->count();
    
    echo "\n🎉 SUCCESS! ASSESSMENTS CREATED! 🎉\n";
    echo "====================================\n\n";
    
    echo "📋 TECHNICAL ASSESSMENT\n";
    echo "- ID: {$techAssessmentId}\n";
    echo "- Name: Technical Assessment - Programming Fundamentals\n";
    echo "- Category: Technical\n";
    echo "- Duration: 20 minutes\n";
    echo "- Questions: {$techCount}\n";
    echo "- Status: Active\n\n";
    
    echo "📋 APTITUDE ASSESSMENT\n";
    echo "- ID: {$aptitudeAssessmentId}\n";
    echo "- Name: Aptitude Assessment - Logical Reasoning\n";
    echo "- Category: Aptitude\n";
    echo "- Duration: 15 minutes\n";
    echo "- Questions: {$aptitudeCount}\n";
    echo "- Status: Active\n\n";
    
    echo "✅ Total Questions Created: " . ($techCount + $aptitudeCount) . "\n";
    echo "✅ Both assessments are ready for students\n";
    echo "✅ All questions have correct answers and options\n";
    echo "✅ Proper index mapping (A=0, B=1, C=2, D=3) maintained\n\n";
    
    echo "🚀 READY TO USE!\n";
    echo "Students can now take these assessments from the student portal.\n";
    echo "Admins can manage them from the admin panel.\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
