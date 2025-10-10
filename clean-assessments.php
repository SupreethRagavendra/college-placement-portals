<?php

/**
 * Script to clean all assessments from database
 * Run this with: php clean-assessments.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Assessment;
use App\Models\Question;

try {
    echo "\n=== CLEANING ALL ASSESSMENTS ===\n\n";
    
    // Get current counts
    $assessmentCount = Assessment::count();
    $studentAssessmentCount = DB::table('student_assessments')->count();
    $studentAnswerCount = DB::table('student_answers')->count();
    $studentResultCount = DB::table('student_results')->count();
    $assessmentQuestionCount = DB::table('assessment_questions')->count();
    
    echo "Current data counts:\n";
    echo "- Assessments: $assessmentCount\n";
    echo "- Student Assessments: $studentAssessmentCount\n";
    echo "- Student Answers: $studentAnswerCount\n";
    echo "- Student Results: $studentResultCount\n";
    echo "- Assessment-Question Links: $assessmentQuestionCount\n\n";
    
    if ($assessmentCount == 0) {
        echo "No assessments found in database. Nothing to delete.\n";
        exit(0);
    }
    
    echo "Are you sure you want to delete ALL assessments and related data? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    
    if ($line !== 'yes') {
        echo "Operation cancelled.\n";
        exit(0);
    }
    
    echo "\nDeleting all assessments...\n";
    
    // Start transaction
    DB::beginTransaction();
    
    try {
        // Delete all assessments (cascades will handle related tables)
        $deletedCount = Assessment::query()->forceDelete();
        
        // Also clean up any orphaned records (shouldn't be necessary with cascade, but just to be safe)
        DB::table('student_answers')->whereNotIn('student_assessment_id', function($query) {
            $query->select('id')->from('student_assessments');
        })->delete();
        
        DB::table('student_assessments')->whereNotIn('assessment_id', function($query) {
            $query->select('id')->from('assessments');
        })->delete();
        
        DB::table('student_results')->whereNotIn('assessment_id', function($query) {
            $query->select('id')->from('assessments');
        })->delete();
        
        DB::table('assessment_questions')->whereNotIn('assessment_id', function($query) {
            $query->select('id')->from('assessments');
        })->delete();
        
        // Commit transaction
        DB::commit();
        
        echo "\n✓ Successfully deleted $deletedCount assessments and all related data.\n";
        
        // Show final counts
        echo "\nFinal data counts:\n";
        echo "- Assessments: " . Assessment::count() . "\n";
        echo "- Student Assessments: " . DB::table('student_assessments')->count() . "\n";
        echo "- Student Answers: " . DB::table('student_answers')->count() . "\n";
        echo "- Student Results: " . DB::table('student_results')->count() . "\n";
        echo "- Assessment-Question Links: " . DB::table('assessment_questions')->count() . "\n";
        
        // Questions should still exist (they're not deleted with assessments)
        $questionCount = Question::count();
        echo "\nNote: Questions table still has $questionCount questions (not deleted as they can exist independently).\n";
        
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
    
    echo "\n=== CLEANUP COMPLETE ===\n\n";
    
} catch (\Exception $e) {
    echo "\n✗ Error occurred: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
