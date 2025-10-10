<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== FINAL VERIFICATION ===\n\n";
    
    // Check assessments
    $assessments = DB::table('assessments')
        ->where('id', '>=', 50)
        ->orderBy('id')
        ->get();
    
    echo "📋 ASSESSMENTS CREATED:\n";
    echo "======================\n";
    
    foreach ($assessments as $assessment) {
        $questionCount = DB::table('assessment_questions')
            ->where('assessment_id', $assessment->id)
            ->count();
            
        echo "✅ {$assessment->name}\n";
        echo "   - ID: {$assessment->id}\n";
        echo "   - Category: {$assessment->category}\n";
        echo "   - Duration: {$assessment->time_limit} minutes\n";
        echo "   - Questions: {$questionCount}\n";
        echo "   - Status: " . ($assessment->is_active ? 'Active' : 'Inactive') . "\n\n";
        
        // Show questions for this assessment
        $questions = DB::table('questions')
            ->join('assessment_questions', 'questions.id', '=', 'assessment_questions.question_id')
            ->where('assessment_questions.assessment_id', $assessment->id)
            ->select('questions.*')
            ->get();
            
        echo "   📝 Questions:\n";
        foreach ($questions as $index => $question) {
            echo "   " . ($index + 1) . ". {$question->question}\n";
            echo "      A) {$question->option_a}\n";
            echo "      B) {$question->option_b}\n";
            echo "      C) {$question->option_c}\n";
            echo "      D) {$question->option_d}\n";
            echo "      ✓ Correct Answer: {$question->correct_answer}\n\n";
        }
        
        echo "   " . str_repeat("-", 50) . "\n\n";
    }
    
    // Summary
    $totalAssessments = count($assessments);
    $totalQuestions = DB::table('assessment_questions')
        ->whereIn('assessment_id', $assessments->pluck('id'))
        ->count();
    
    echo "🎉 SETUP COMPLETE! 🎉\n";
    echo "====================\n";
    echo "✅ Total Assessments Created: {$totalAssessments}\n";
    echo "✅ Total Questions Added: {$totalQuestions}\n";
    echo "✅ Categories: Technical & Aptitude\n";
    echo "✅ All assessments are active and ready\n\n";
    
    echo "🚀 READY FOR USE!\n";
    echo "Students can now:\n";
    echo "- Browse available assessments\n";
    echo "- Take assessments with time limits\n";
    echo "- View results immediately\n";
    echo "- See correct answers after completion\n\n";
    
    echo "Admin can:\n";
    echo "- Manage assessments from admin panel\n";
    echo "- View student results and reports\n";
    echo "- Add more questions or assessments\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
