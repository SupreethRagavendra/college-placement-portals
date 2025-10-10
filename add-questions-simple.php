<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Question;

try {
    echo "=== ADDING QUESTIONS TO EXISTING ASSESSMENTS ===\n\n";
    
    // Get the assessments we created
    $techAssessment = DB::table('assessments')->where('category', 'Technical')->where('name', 'LIKE', '%Programming%')->first();
    $aptitudeAssessment = DB::table('assessments')->where('category', 'Aptitude')->where('name', 'LIKE', '%Logical%')->first();
    
    if (!$techAssessment) {
        echo "❌ Technical assessment not found\n";
        exit(1);
    }
    
    if (!$aptitudeAssessment) {
        echo "❌ Aptitude assessment not found\n";
        exit(1);
    }
    
    echo "Found assessments:\n";
    echo "- Technical: ID {$techAssessment->id}\n";
    echo "- Aptitude: ID {$aptitudeAssessment->id}\n\n";
    
    // Get category IDs
    $technicalCategoryId = DB::table('categories')->where('name', 'Technical')->value('id');
    $aptitudeCategoryId = DB::table('categories')->where('name', 'Aptitude')->value('id');
    
    DB::beginTransaction();
    
    // Technical Questions - using minimal required fields
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
            'question' => 'What is the time complexity of binary search?',
            'option_a' => 'O(n)',
            'option_b' => 'O(n²)',
            'option_c' => 'O(log n)',
            'option_d' => 'O(1)',
            'correct_answer' => 'C'
        ],
        [
            'question' => 'Which language supports multiple paradigms?',
            'option_a' => 'C only',
            'option_b' => 'Java only',
            'option_c' => 'Python only',
            'option_d' => 'JavaScript',
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
        try {
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
                'assessment_id' => $techAssessment->id,
                'question_id' => $question->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            echo "  ✓ Question " . ($index + 1) . ": ID {$question->id}\n";
            
        } catch (\Exception $e) {
            echo "  ❌ Failed to create question " . ($index + 1) . ": " . $e->getMessage() . "\n";
        }
    }
    
    // Aptitude Questions
    $aptitudeQuestions = [
        [
            'question' => 'If all roses are flowers and some flowers fade quickly, which is true?',
            'option_a' => 'All roses fade quickly',
            'option_b' => 'Some roses fade quickly',
            'option_c' => 'No roses fade quickly',
            'option_d' => 'Cannot be determined',
            'correct_answer' => 'D'
        ],
        [
            'question' => 'Next number in sequence 2, 6, 12, 20, 30, ?',
            'option_a' => '40',
            'option_b' => '42',
            'option_c' => '44',
            'option_d' => '36',
            'correct_answer' => 'B'
        ],
        [
            'question' => 'If A=1, B=2, C=3, what is FACE?',
            'option_a' => '6134',
            'option_b' => '6135',
            'option_c' => '6133',
            'option_d' => '6132',
            'correct_answer' => 'B'
        ],
        [
            'question' => 'Train crosses 200m platform in 30s. Train=100m. Distance?',
            'option_a' => '300 meters',
            'option_b' => '500 meters',
            'option_c' => '200 meters',
            'option_d' => '100 meters',
            'correct_answer' => 'A'
        ]
    ];
    
    echo "\nCreating Aptitude Questions:\n";
    foreach ($aptitudeQuestions as $index => $q) {
        try {
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
                'assessment_id' => $aptitudeAssessment->id,
                'question_id' => $question->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            echo "  ✓ Question " . ($index + 1) . ": ID {$question->id}\n";
            
        } catch (\Exception $e) {
            echo "  ❌ Failed to create question " . ($index + 1) . ": " . $e->getMessage() . "\n";
        }
    }
    
    DB::commit();
    
    echo "\n=== FINAL VERIFICATION ===\n";
    
    // Count questions per assessment
    $techQuestionCount = DB::table('assessment_questions')->where('assessment_id', $techAssessment->id)->count();
    $aptitudeQuestionCount = DB::table('assessment_questions')->where('assessment_id', $aptitudeAssessment->id)->count();
    
    echo "✅ Technical Assessment: {$techQuestionCount} questions\n";
    echo "✅ Aptitude Assessment: {$aptitudeQuestionCount} questions\n";
    echo "\n🎉 SUCCESS! Both assessments are ready with questions!\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
