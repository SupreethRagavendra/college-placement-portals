<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== ADDING QUESTIONS WITH ERROR DETAILS ===\n\n";
    
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
    
    echo "Found assessments:\n";
    echo "- Technical: ID {$techAssessment->id}\n";
    echo "- Aptitude: ID {$aptitudeAssessment->id}\n\n";
    
    // Get category IDs
    $technicalCategoryId = DB::table('categories')->where('name', 'Technical')->value('id');
    $aptitudeCategoryId = DB::table('categories')->where('name', 'Aptitude')->value('id');
    
    echo "Category IDs: Technical={$technicalCategoryId}, Aptitude={$aptitudeCategoryId}\n\n";
    
    // Try to add ONE question with full error details
    echo "Attempting to add a test question...\n";
    
    try {
        $questionId = DB::table('questions')->insertGetId([
            'question' => 'Which data structure uses LIFO?',
            'category_id' => $technicalCategoryId,
            'option_a' => 'Queue',
            'option_b' => 'Stack',
            'option_c' => 'Array',
            'option_d' => 'Linked List',
            'correct_answer' => 'B',
            'correct_option' => 1,
            'is_active' => true,
            'question_type' => 'mcq',
            'marks' => 5,
            'time_per_question' => 60,
            'difficulty_level' => 'medium',
            'order' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "✅ SUCCESS! Question created with ID: {$questionId}\n";
        
        // Link to assessment
        DB::table('assessment_questions')->insert([
            'assessment_id' => $techAssessment->id,
            'question_id' => $questionId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "✅ Linked to assessment\n";
        
        // If this works, add the rest
        echo "\nAdding remaining questions...\n\n";
        
        // Technical Questions
        $techQuestions = [
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
                'question' => 'JavaScript supports which paradigm?',
                'option_a' => 'OOP only',
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
            $qId = DB::table('questions')->insertGetId([
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
                'order' => $index + 2,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            DB::table('assessment_questions')->insert([
                'assessment_id' => $techAssessment->id,
                'question_id' => $qId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            echo "✅ Technical Q" . ($index + 2) . " (ID: {$qId})\n";
        }
        
        // Aptitude Questions
        $aptitudeQuestions = [
            [
                'question' => 'All roses are flowers, some flowers fade. Which is true?',
                'option_a' => 'All roses fade',
                'option_b' => 'Some roses fade',
                'option_c' => 'No roses fade',
                'option_d' => 'Cannot determine',
                'correct_answer' => 'D',
                'correct_option' => 3
            ],
            [
                'question' => 'Next in sequence: 2, 6, 12, 20, 30, ?',
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
        
        echo "\n";
        foreach ($aptitudeQuestions as $index => $q) {
            $qId = DB::table('questions')->insertGetId([
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
            
            DB::table('assessment_questions')->insert([
                'assessment_id' => $aptitudeAssessment->id,
                'question_id' => $qId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            echo "✅ Aptitude Q" . ($index + 1) . " (ID: {$qId})\n";
        }
        
        echo "\n🎉 ALL QUESTIONS ADDED SUCCESSFULLY!\n";
        
        // Verify
        $techCount = DB::table('assessment_questions')->where('assessment_id', $techAssessment->id)->count();
        $aptCount = DB::table('assessment_questions')->where('assessment_id', $aptitudeAssessment->id)->count();
        
        echo "\n📊 FINAL COUNT:\n";
        echo "✅ Technical Assessment: {$techCount} questions\n";
        echo "✅ Aptitude Assessment: {$aptCount} questions\n";
        echo "✅ Total: " . ($techCount + $aptCount) . " questions\n";
        
    } catch (\Exception $e) {
        echo "\n❌ DETAILED ERROR:\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n\n";
        
        // Try to get more details
        if (method_exists($e, 'errorInfo')) {
            echo "Error Info: " . print_r($e->errorInfo, true) . "\n";
        }
        
        // Check if it's a SQL error
        if ($e instanceof \PDOException) {
            echo "PDO Error Code: " . $e->getCode() . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
}
