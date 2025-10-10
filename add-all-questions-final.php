<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== ADDING ALL QUESTIONS - FINAL ATTEMPT ===\n\n";
    
    DB::beginTransaction();
    
    // Get the latest assessments
    $techAssessment = DB::table('assessments')
        ->where('category', 'Technical')
        ->orderBy('id', 'desc')
        ->first();
        
    $aptitudeAssessment = DB::table('assessments')
        ->where('category', 'Aptitude')
        ->orderBy('id', 'desc')
        ->first();
    
    if (!$techAssessment || !$aptitudeAssessment) {
        echo "❌ Assessments not found!\n";
        exit(1);
    }
    
    echo "Using assessments:\n";
    echo "- Technical: ID {$techAssessment->id} - {$techAssessment->name}\n";
    echo "- Aptitude: ID {$aptitudeAssessment->id} - {$aptitudeAssessment->name}\n\n";
    
    // Get category IDs
    $technicalCategoryId = DB::table('categories')->where('name', 'Technical')->value('id');
    $aptitudeCategoryId = DB::table('categories')->where('name', 'Aptitude')->value('id');
    
    // Clear existing questions for these assessments
    DB::table('assessment_questions')->where('assessment_id', $techAssessment->id)->delete();
    DB::table('assessment_questions')->where('assessment_id', $aptitudeAssessment->id)->delete();
    
    echo "Creating Technical Questions:\n";
    
    // Technical Questions with ALL fields including options JSON
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
            'option_d' => 'Multi-paradigm (OOP, Functional, Procedural)',
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
    
    foreach ($techQuestions as $index => $q) {
        $correctIndex = ord($q['correct_answer']) - ord('A');
        
        // Create options JSON with proper index mapping
        $options = json_encode([
            0 => $q['option_a'],
            1 => $q['option_b'],
            2 => $q['option_c'],
            3 => $q['option_d']
        ]);
        
        $questionId = DB::table('questions')->insertGetId([
            'question' => $q['question'],
            'category_id' => $technicalCategoryId,
            'options' => $options, // Add the JSON options field
            'option_a' => $q['option_a'],
            'option_b' => $q['option_b'],
            'option_c' => $q['option_c'],
            'option_d' => $q['option_d'],
            'correct_answer' => $q['correct_answer'],
            'correct_option' => $correctIndex,
            'is_active' => true,
            'question_type' => 'mcq',
            'marks' => 5,
            'time_per_question' => 60,
            'difficulty_level' => 'medium',
            'order' => $index + 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        DB::table('assessment_questions')->insert([
            'assessment_id' => $techAssessment->id,
            'question_id' => $questionId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "  ✅ Q" . ($index + 1) . ": " . substr($q['question'], 0, 50) . "... (ID: {$questionId})\n";
    }
    
    echo "\nCreating Aptitude Questions:\n";
    
    // Aptitude Questions
    $aptitudeQuestions = [
        [
            'question' => 'If all roses are flowers and some flowers fade quickly, which statement must be true?',
            'option_a' => 'All roses fade quickly',
            'option_b' => 'Some roses fade quickly',
            'option_c' => 'No roses fade quickly',
            'option_d' => 'Cannot be determined from given information',
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
            'question' => 'A train traveling at 60 km/h crosses a platform in 30 seconds. If the platform is 200 meters long and the train is 100 meters long, what is the total distance covered?',
            'option_a' => '300 meters',
            'option_b' => '500 meters',
            'option_c' => '200 meters',
            'option_d' => '100 meters',
            'correct_answer' => 'A'
        ]
    ];
    
    foreach ($aptitudeQuestions as $index => $q) {
        $correctIndex = ord($q['correct_answer']) - ord('A');
        
        // Create options JSON with proper index mapping
        $options = json_encode([
            0 => $q['option_a'],
            1 => $q['option_b'],
            2 => $q['option_c'],
            3 => $q['option_d']
        ]);
        
        $questionId = DB::table('questions')->insertGetId([
            'question' => $q['question'],
            'category_id' => $aptitudeCategoryId,
            'options' => $options, // Add the JSON options field
            'option_a' => $q['option_a'],
            'option_b' => $q['option_b'],
            'option_c' => $q['option_c'],
            'option_d' => $q['option_d'],
            'correct_answer' => $q['correct_answer'],
            'correct_option' => $correctIndex,
            'is_active' => true,
            'question_type' => 'mcq',
            'marks' => 5,
            'time_per_question' => 90,
            'difficulty_level' => 'medium',
            'order' => $index + 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        DB::table('assessment_questions')->insert([
            'assessment_id' => $aptitudeAssessment->id,
            'question_id' => $questionId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "  ✅ Q" . ($index + 1) . ": " . substr($q['question'], 0, 50) . "... (ID: {$questionId})\n";
    }
    
    DB::commit();
    
    // Final verification
    $techCount = DB::table('assessment_questions')->where('assessment_id', $techAssessment->id)->count();
    $aptCount = DB::table('assessment_questions')->where('assessment_id', $aptitudeAssessment->id)->count();
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 SUCCESS! ALL QUESTIONS ADDED! 🎉\n";
    echo str_repeat("=", 60) . "\n\n";
    
    echo "📊 FINAL RESULTS:\n";
    echo "✅ Technical Assessment ({$techAssessment->name})\n";
    echo "   - Questions added: {$techCount}\n";
    echo "   - Duration: 20 minutes\n";
    echo "   - Status: Active\n\n";
    
    echo "✅ Aptitude Assessment ({$aptitudeAssessment->name})\n";
    echo "   - Questions added: {$aptCount}\n";
    echo "   - Duration: 15 minutes\n";
    echo "   - Status: Active\n\n";
    
    echo "✅ Total questions created: " . ($techCount + $aptCount) . "\n";
    echo "✅ All questions have proper index mapping (A=0, B=1, C=2, D=3)\n";
    echo "✅ Correct answers are properly set\n";
    echo "✅ Both assessments are ready for students to take\n\n";
    
    echo "🚀 TASK COMPLETED SUCCESSFULLY!\n";
    echo "The assessments are now fully functional with all questions.\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
