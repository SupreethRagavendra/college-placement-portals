<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== FINAL COMPLETE SETUP ===\n\n";
    
    // Check if we have the assessments
    $techAssessment = DB::table('assessments')->where('id', 50)->first();
    $aptitudeAssessment = DB::table('assessments')->where('id', 51)->first();
    
    if (!$techAssessment || !$aptitudeAssessment) {
        echo "❌ Assessments not found. Let me create them first.\n";
        
        // Get admin and categories
        $admin = DB::table('users')->first();
        $technicalCategoryId = DB::table('categories')->where('name', 'Technical')->value('id');
        $aptitudeCategoryId = DB::table('categories')->where('name', 'Aptitude')->value('id');
        
        // Create assessments
        $techAssessmentId = DB::table('assessments')->insertGetId([
            'name' => 'Technical Assessment - Programming Fundamentals',
            'description' => 'Test your programming knowledge',
            'category' => 'Technical',
            'time_limit' => 20,
            'is_active' => true,
            'created_by' => $admin->id,
            'status' => 'active',
            'pass_percentage' => 50,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $aptitudeAssessmentId = DB::table('assessments')->insertGetId([
            'name' => 'Aptitude Assessment - Logical Reasoning', 
            'description' => 'Test your logical reasoning skills',
            'category' => 'Aptitude',
            'time_limit' => 15,
            'is_active' => true,
            'created_by' => $admin->id,
            'status' => 'active',
            'pass_percentage' => 50,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "✅ Created assessments: Tech({$techAssessmentId}), Aptitude({$aptitudeAssessmentId})\n";
    } else {
        $techAssessmentId = $techAssessment->id;
        $aptitudeAssessmentId = $aptitudeAssessment->id;
        echo "✅ Found existing assessments: Tech({$techAssessmentId}), Aptitude({$aptitudeAssessmentId})\n";
    }
    
    // Get category IDs
    $technicalCategoryId = DB::table('categories')->where('name', 'Technical')->value('id');
    $aptitudeCategoryId = DB::table('categories')->where('name', 'Aptitude')->value('id');
    
    echo "Category IDs: Technical({$technicalCategoryId}), Aptitude({$aptitudeCategoryId})\n\n";
    
    // Add questions using raw SQL with minimal required fields
    echo "Adding Technical Questions...\n";
    
    // Technical Question 1
    $q1Id = DB::table('questions')->insertGetId([
        'question' => 'Which data structure uses LIFO principle?',
        'category_id' => $technicalCategoryId,
        'option_a' => 'Queue',
        'option_b' => 'Stack',
        'option_c' => 'Array',
        'option_d' => 'Linked List',
        'correct_answer' => 'B',
        'correct_option' => 1,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    DB::table('assessment_questions')->insert([
        'assessment_id' => $techAssessmentId,
        'question_id' => $q1Id,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Q1: LIFO principle (ID: {$q1Id})\n";
    
    // Technical Question 2
    $q2Id = DB::table('questions')->insertGetId([
        'question' => 'Time complexity of binary search?',
        'category_id' => $technicalCategoryId,
        'option_a' => 'O(n)',
        'option_b' => 'O(n²)',
        'option_c' => 'O(log n)',
        'option_d' => 'O(1)',
        'correct_answer' => 'C',
        'correct_option' => 2,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    DB::table('assessment_questions')->insert([
        'assessment_id' => $techAssessmentId,
        'question_id' => $q2Id,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Q2: Binary search complexity (ID: {$q2Id})\n";
    
    // Technical Question 3
    $q3Id = DB::table('questions')->insertGetId([
        'question' => 'JavaScript supports which paradigm?',
        'category_id' => $technicalCategoryId,
        'option_a' => 'OOP only',
        'option_b' => 'Functional only',
        'option_c' => 'Procedural only',
        'option_d' => 'Multi-paradigm',
        'correct_answer' => 'D',
        'correct_option' => 3,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    DB::table('assessment_questions')->insert([
        'assessment_id' => $techAssessmentId,
        'question_id' => $q3Id,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Q3: JavaScript paradigm (ID: {$q3Id})\n";
    
    // Technical Question 4
    $q4Id = DB::table('questions')->insertGetId([
        'question' => 'What does SQL stand for?',
        'category_id' => $technicalCategoryId,
        'option_a' => 'Structured Query Language',
        'option_b' => 'Simple Question Language',
        'option_c' => 'Sequential Query Language',
        'option_d' => 'System Query Language',
        'correct_answer' => 'A',
        'correct_option' => 0,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    DB::table('assessment_questions')->insert([
        'assessment_id' => $techAssessmentId,
        'question_id' => $q4Id,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Q4: SQL meaning (ID: {$q4Id})\n";
    
    echo "\nAdding Aptitude Questions...\n";
    
    // Aptitude Question 1
    $a1Id = DB::table('questions')->insertGetId([
        'question' => 'All roses are flowers, some flowers fade quickly. Which is true?',
        'category_id' => $aptitudeCategoryId,
        'option_a' => 'All roses fade quickly',
        'option_b' => 'Some roses fade quickly',
        'option_c' => 'No roses fade quickly',
        'option_d' => 'Cannot be determined',
        'correct_answer' => 'D',
        'correct_option' => 3,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    DB::table('assessment_questions')->insert([
        'assessment_id' => $aptitudeAssessmentId,
        'question_id' => $a1Id,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ A1: Logical reasoning (ID: {$a1Id})\n";
    
    // Aptitude Question 2
    $a2Id = DB::table('questions')->insertGetId([
        'question' => 'Next in sequence: 2, 6, 12, 20, 30, ?',
        'category_id' => $aptitudeCategoryId,
        'option_a' => '40',
        'option_b' => '42',
        'option_c' => '44',
        'option_d' => '36',
        'correct_answer' => 'B',
        'correct_option' => 1,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    DB::table('assessment_questions')->insert([
        'assessment_id' => $aptitudeAssessmentId,
        'question_id' => $a2Id,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ A2: Number sequence (ID: {$a2Id})\n";
    
    // Aptitude Question 3
    $a3Id = DB::table('questions')->insertGetId([
        'question' => 'If A=1, B=2, C=3, what is FACE?',
        'category_id' => $aptitudeCategoryId,
        'option_a' => '6134',
        'option_b' => '6135',
        'option_c' => '6133',
        'option_d' => '6132',
        'correct_answer' => 'B',
        'correct_option' => 1,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    DB::table('assessment_questions')->insert([
        'assessment_id' => $aptitudeAssessmentId,
        'question_id' => $a3Id,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ A3: Letter coding (ID: {$a3Id})\n";
    
    // Aptitude Question 4
    $a4Id = DB::table('questions')->insertGetId([
        'question' => 'Train crosses 200m platform in 30s. Train=100m. Distance?',
        'category_id' => $aptitudeCategoryId,
        'option_a' => '300 meters',
        'option_b' => '500 meters',
        'option_c' => '200 meters',
        'option_d' => '100 meters',
        'correct_answer' => 'A',
        'correct_option' => 0,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    DB::table('assessment_questions')->insert([
        'assessment_id' => $aptitudeAssessmentId,
        'question_id' => $a4Id,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ A4: Train problem (ID: {$a4Id})\n";
    
    // Final verification
    $techCount = DB::table('assessment_questions')->where('assessment_id', $techAssessmentId)->count();
    $aptitudeCount = DB::table('assessment_questions')->where('assessment_id', $aptitudeAssessmentId)->count();
    
    echo "\n🎉 COMPLETE SUCCESS! 🎉\n";
    echo "========================\n";
    echo "✅ Technical Assessment: {$techCount} questions\n";
    echo "✅ Aptitude Assessment: {$aptitudeCount} questions\n";
    echo "✅ Total: " . ($techCount + $aptitudeCount) . " questions created\n\n";
    
    echo "📋 ASSESSMENT DETAILS:\n";
    echo "======================\n";
    echo "1. Technical Assessment - Programming Fundamentals\n";
    echo "   - Duration: 20 minutes\n";
    echo "   - Questions: {$techCount}\n";
    echo "   - Topics: Data structures, algorithms, programming concepts\n\n";
    
    echo "2. Aptitude Assessment - Logical Reasoning\n";
    echo "   - Duration: 15 minutes\n";
    echo "   - Questions: {$aptitudeCount}\n";
    echo "   - Topics: Logic, sequences, coding, problem solving\n\n";
    
    echo "🚀 READY FOR USE!\n";
    echo "Both assessments are now active and available for students.\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
