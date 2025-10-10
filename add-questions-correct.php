<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== ADDING QUESTIONS WITH CORRECT STRUCTURE ===\n\n";
    
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
    echo "- Technical: ID {$techAssessment->id}\n";
    echo "- Aptitude: ID {$aptitudeAssessment->id}\n\n";
    
    // Get category IDs
    $technicalCategoryId = DB::table('categories')->where('name', 'Technical')->value('id');
    $aptitudeCategoryId = DB::table('categories')->where('name', 'Aptitude')->value('id');
    
    // Clear existing questions for these assessments
    DB::table('assessment_questions')->where('assessment_id', $techAssessment->id)->delete();
    DB::table('assessment_questions')->where('assessment_id', $aptitudeAssessment->id)->delete();
    
    echo "Creating Technical Questions:\n";
    
    // Technical Questions
    $techQuestions = [
        ['question' => 'Which data structure uses LIFO (Last In First Out) principle?', 'options' => ['Queue', 'Stack', 'Array', 'Linked List'], 'correct' => 1],
        ['question' => 'What is the time complexity of binary search in a sorted array?', 'options' => ['O(n)', 'O(n²)', 'O(log n)', 'O(1)'], 'correct' => 2],
        ['question' => 'Which programming paradigm does JavaScript primarily support?', 'options' => ['Object-Oriented only', 'Functional only', 'Procedural only', 'Multi-paradigm'], 'correct' => 3],
        ['question' => 'What does SQL stand for?', 'options' => ['Structured Query Language', 'Simple Question Language', 'Sequential Query Language', 'System Query Language'], 'correct' => 0]
    ];
    
    foreach ($techQuestions as $index => $q) {
        $letters = ['A', 'B', 'C', 'D'];
        
        $questionId = DB::table('questions')->insertGetId([
            'question' => $q['question'],
            'category_id' => $technicalCategoryId,
            'options' => json_encode($q['options']),
            'correct_option' => $q['correct'],
            'correct_answer' => $letters[$q['correct']],
            'is_active' => true,
            'question_type' => 'mcq',
            'marks' => 5,
            'time_per_question' => 60,
            'difficulty_level' => 'medium',
            'order' => $index + 1
        ]);
        
        DB::table('assessment_questions')->insert([
            'assessment_id' => $techAssessment->id,
            'question_id' => $questionId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "  ✅ Q" . ($index + 1) . " (ID: {$questionId})\n";
    }
    
    echo "\nCreating Aptitude Questions:\n";
    
    // Aptitude Questions
    $aptitudeQuestions = [
        ['question' => 'If all roses are flowers and some flowers fade quickly, which statement must be true?', 'options' => ['All roses fade quickly', 'Some roses fade quickly', 'No roses fade quickly', 'Cannot be determined'], 'correct' => 3],
        ['question' => 'What is the next number in the sequence: 2, 6, 12, 20, 30, ?', 'options' => ['40', '42', '44', '36'], 'correct' => 1],
        ['question' => 'If A is coded as 1, B as 2, C as 3, what is the code for FACE?', 'options' => ['6134', '6135', '6133', '6132'], 'correct' => 1],
        ['question' => 'A train crosses a 200m platform in 30 seconds. If train length is 100m, total distance covered?', 'options' => ['300 meters', '500 meters', '200 meters', '100 meters'], 'correct' => 0]
    ];
    
    foreach ($aptitudeQuestions as $index => $q) {
        $letters = ['A', 'B', 'C', 'D'];
        
        $questionId = DB::table('questions')->insertGetId([
            'question' => $q['question'],
            'category_id' => $aptitudeCategoryId,
            'options' => json_encode($q['options']),
            'correct_option' => $q['correct'],
            'correct_answer' => $letters[$q['correct']],
            'is_active' => true,
            'question_type' => 'mcq',
            'marks' => 5,
            'time_per_question' => 90,
            'difficulty_level' => 'medium',
            'order' => $index + 1
        ]);
        
        DB::table('assessment_questions')->insert([
            'assessment_id' => $aptitudeAssessment->id,
            'question_id' => $questionId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "  ✅ Q" . ($index + 1) . " (ID: {$questionId})\n";
    }
    
    DB::commit();
    
    // Final verification
    $techCount = DB::table('assessment_questions')->where('assessment_id', $techAssessment->id)->count();
    $aptCount = DB::table('assessment_questions')->where('assessment_id', $aptitudeAssessment->id)->count();
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 SUCCESS! ALL QUESTIONS ADDED! 🎉\n";
    echo str_repeat("=", 60) . "\n\n";
    
    echo "📊 FINAL RESULTS:\n";
    echo "✅ Technical Assessment: {$techCount} questions\n";
    echo "✅ Aptitude Assessment: {$aptCount} questions\n";
    echo "✅ Total: " . ($techCount + $aptCount) . " questions\n\n";
    
    echo "🚀 TASK COMPLETED!\n";
    echo "Both assessments are now ready with all questions.\n";
    echo "Students can take these assessments from the portal.\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
