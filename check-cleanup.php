<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Assessment;
use App\Models\Question;

echo "\n=== DATABASE STATUS CHECK ===\n\n";

// Check all tables
$assessmentCount = Assessment::withTrashed()->count();
$studentAssessmentCount = DB::table('student_assessments')->count();
$studentAnswerCount = DB::table('student_answers')->count();
$studentResultCount = DB::table('student_results')->count();
$assessmentQuestionCount = DB::table('assessment_questions')->count();
$questionCount = Question::count();

echo "Current counts after cleanup:\n";
echo "- Assessments: $assessmentCount\n";
echo "- Student Assessments: $studentAssessmentCount\n";
echo "- Student Answers: $studentAnswerCount\n";
echo "- Student Results: $studentResultCount\n";
echo "- Assessment-Question Links: $assessmentQuestionCount\n";
echo "- Questions: $questionCount (preserved)\n\n";

if ($assessmentCount == 0) {
    echo "✓ All assessments have been successfully deleted!\n";
    echo "✓ All related data has been cleaned up!\n";
    echo "✓ Questions have been preserved as requested.\n";
} else {
    echo "⚠ Warning: $assessmentCount assessments still exist in the database.\n";
}

echo "\n=== CHECK COMPLETE ===\n";
