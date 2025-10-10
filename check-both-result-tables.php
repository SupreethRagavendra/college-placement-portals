<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\StudentAssessment;
use App\Models\StudentResult;

echo "\n==========================================\n";
echo "CHECKING BOTH RESULT TABLES\n";
echo "==========================================\n\n";

$user = User::where('name', 'LIKE', '%Supreeth%')->first();

if (!$user) {
    echo "❌ User not found\n";
    exit;
}

echo "User: {$user->name} (ID: {$user->id})\n\n";

// Check student_assessments table
echo "--- TABLE 1: student_assessments ---\n";
$assessments = StudentAssessment::where('student_id', $user->id)
    ->with('assessment')
    ->get();

if ($assessments->isEmpty()) {
    echo "❌ No data in student_assessments\n\n";
} else {
    echo "✅ Found {$assessments->count()} record(s):\n";
    foreach ($assessments as $a) {
        echo "- {$a->assessment->name}: {$a->obtained_marks}/{$a->total_marks} ({$a->percentage}%) - {$a->status}\n";
    }
    echo "\n";
}

// Check student_results table
echo "--- TABLE 2: student_results ---\n";
$results = StudentResult::where('student_id', $user->id)
    ->with('assessment')
    ->get();

if ($results->isEmpty()) {
    echo "❌ No data in student_results\n\n";
} else {
    echo "✅ Found {$results->count()} record(s):\n";
    foreach ($results as $r) {
        $percentage = $r->total_questions > 0 ? round(($r->score / $r->total_questions) * 100, 1) : 0;
        echo "- {$r->assessment->name}: {$r->score}/{$r->total_questions} ({$percentage}%)\n";
        echo "  Submitted: {$r->submitted_at}\n";
    }
    echo "\n";
}

echo "==========================================\n";
echo "DIAGNOSIS\n";
echo "==========================================\n\n";

if ($assessments->isEmpty() && $results->isEmpty()) {
    echo "❌ NO DATA IN EITHER TABLE!\n\n";
    echo "If you completed a test, the data didn't save.\n";
    echo "Possible reasons:\n";
    echo "1. Test was started but not submitted\n";
    echo "2. Database write error during submission\n";
    echo "3. Different database being used\n";
} else if (!$results->isEmpty()) {
    echo "✅ DATA FOUND IN student_results table!\n\n";
    echo "ISSUE: ChatbotController is looking in student_assessments table\n";
    echo "SOLUTION: Update ChatbotController to also check student_results table\n";
} else {
    echo "✅ DATA FOUND IN student_assessments table!\n\n";
    echo "The RAG should be able to see this data.\n";
}

echo "\n==========================================\n\n";

?>

