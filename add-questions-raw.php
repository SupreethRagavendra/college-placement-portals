<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== ADDING QUESTIONS WITH RAW SQL ===\n\n";
    
    DB::beginTransaction();
    
    // Get the assessments we created
    $techAssessment = DB::table('assessments')->where('name', 'LIKE', '%Technical Assessment - Programming%')->first();
    $aptitudeAssessment = DB::table('assessments')->where('name', 'LIKE', '%Aptitude Assessment - Logical%')->first();
    
    if (!$techAssessment || !$aptitudeAssessment) {
        echo "❌ Assessments not found\n";
        exit(1);
    }
    
    echo "Found assessments:\n";
    echo "- Technical: ID {$techAssessment->id}\n";
    echo "- Aptitude: ID {$aptitudeAssessment->id}\n\n";
    
    // Get category IDs
    $technicalCategoryId = DB::table('categories')->where('name', 'Technical')->value('id');
    $aptitudeCategoryId = DB::table('categories')->where('name', 'Aptitude')->value('id');
    
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
            'question' => 'What is the time complexity of binary search?',
            'option_a' => 'O(n)',
            'option_b' => 'O(n²)',
            'option_c' => 'O(log n)',
            'option_d' => 'O(1)',
            'correct_answer' => 'C',
            'correct_option' => 2
        ],
        [
            'question' => 'Which language supports multiple paradigms?',
            'option_a' => 'C only',
            'option_b' => 'Java only',
            'option_c' => 'Python only',
            'option_d' => 'JavaScript',
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
    
    echo "Creating Technical Questions:\n";
    foreach ($techQuestions as $index => $q) {
        $questionId = DB::table('questions')->insertGetId([
            'question' => $q['question'],
            'category_id' => $technicalCategoryId,
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
            'assessment_id' => $techAssessment->id,
            'question_id' => $questionId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "  ✅ Question " . ($index + 1) . " (ID: {$questionId})\n";
    }
    
    // Aptitude Questions
    $aptitudeQuestions = [
        [
            'question' => 'If all roses are flowers and some flowers fade quickly, which is true?',
            'option_a' => 'All roses fade quickly',
            'option_b' => 'Some roses fade quickly',
            'option_c' => 'No roses fade quickly',
            'option_d' => 'Cannot be determined',
            'correct_answer' => 'D',
            'correct_option' => 3
        ],
        [
            'question' => 'Next number in sequence: 2, 6, 12, 20, 30, ?',
            'option_a' => '40',
            'option_b' => '42',
            'option_c' => '44',
            'option_d' => '36',
            'correct_answer' => 'B',
            'correct_option' => 1
        ],
        [
            'question' => 'If A=1, B=2, C=3, what is FACE?',
            'option_a' => '6134',
            'option_b' => '6135',
            'option_c' => '6133',
            'option_d' => '6132',
            'correct_answer' => 'B',
            'correct_option' => 1
        ],
        [
            'question' => 'Train crosses 200m platform in 30s. Train=100m. Distance?',
            'option_a' => '300 meters',
            'option_b' => '500 meters',
            'option_c' => '200 meters',
            'option_d' => '100 meters',
            'correct_answer' => 'A',
            'correct_option' => 0
        ]
    ];
    
    echo "\nCreating Aptitude Questions:\n";
    foreach ($aptitudeQuestions as $index => $q) {
        $questionId = DB::table('questions')->insertGetId([
            'question' => $q['question'],
            'category_id' => $aptitudeCategoryId,
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
            'assessment_id' => $aptitudeAssessment->id,
            'question_id' => $questionId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "  ✅ Question " . ($index + 1) . " (ID: {$questionId})\n";
    }
    
    DB::commit();
    
    echo "\n🎉 SUCCESS! Added all questions to assessments\n\n";
    
    // Final verification
    $techQuestionCount = DB::table('assessment_questions')->where('assessment_id', $techAssessment->id)->count();
    $aptitudeQuestionCount = DB::table('assessment_questions')->where('assessment_id', $aptitudeAssessment->id)->count();
    
    echo "📊 FINAL STATUS:\n";
    echo "✅ Technical Assessment: {$techQuestionCount} questions\n";
    echo "✅ Aptitude Assessment: {$aptitudeQuestionCount} questions\n";
    echo "\n🚀 Both assessments are now complete and ready for use!\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
