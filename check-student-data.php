<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StudentAssessment;
use App\Models\Assessment;

echo "\n==========================================\n";
echo "CHECKING STUDENT DATA FOR RAG CONTEXT\n";
echo "==========================================\n\n";

$studentId = 49; // Supreeth's ID

echo "Student ID: $studentId\n\n";

// Check completed assessments
echo "--- COMPLETED ASSESSMENTS ---\n";
$completed = StudentAssessment::where('student_id', $studentId)
    ->where('status', 'completed')
    ->with('assessment')
    ->get();

if ($completed->isEmpty()) {
    echo "❌ No completed assessments found!\n";
    echo "This is why RAG says 'I don't have access to your test history'\n\n";
} else {
    echo "✅ Found {$completed->count()} completed assessment(s):\n\n";
    foreach ($completed as $result) {
        $name = $result->assessment->name ?? 'Unknown';
        $score = $result->obtained_marks;
        $total = $result->total_marks;
        $percentage = $result->percentage;
        $status = $result->pass_status;
        
        echo "📝 {$name}\n";
        echo "   Score: {$score}/{$total} ({$percentage}%)\n";
        echo "   Status: {$status}\n";
        echo "   Submitted: {$result->submit_time}\n";
        echo "   ---\n";
    }
}

// Check all student assessments (any status)
echo "\n--- ALL STUDENT ASSESSMENTS (Any Status) ---\n";
$all = StudentAssessment::where('student_id', $studentId)->get();

if ($all->isEmpty()) {
    echo "❌ Student has never started any assessment!\n\n";
    echo "SOLUTION: Student needs to complete at least one assessment first.\n";
} else {
    echo "Found {$all->count()} assessment attempt(s):\n\n";
    foreach ($all as $attempt) {
        $name = $attempt->assessment->name ?? 'Unknown';
        echo "- {$name}: Status = {$attempt->status}\n";
    }
}

echo "\n==========================================\n";
echo "DIAGNOSIS\n";
echo "==========================================\n\n";

if ($completed->isEmpty()) {
    echo "⚠️  ISSUE: Student has no completed assessments\n\n";
    echo "Why RAG can't show results:\n";
    echo "1. Student hasn't completed any tests yet\n";
    echo "2. Context is empty (no data to pass to RAG)\n";
    echo "3. RAG correctly says 'I don't have access'\n\n";
    
    echo "SOLUTIONS:\n";
    echo "1. Student should complete an assessment first\n";
    echo "2. OR RAG should say 'You haven't completed any assessments yet'\n";
    echo "   instead of 'I don't have access'\n\n";
    
    echo "Let me check what context is being sent to RAG...\n";
} else {
    echo "✅ Student HAS completed assessments!\n";
    echo "RAG should be able to show this data.\n\n";
    echo "If RAG still says 'no access', the issue is:\n";
    echo "1. Context not being passed properly to RAG\n";
    echo "2. RAG not using the context correctly\n";
}

echo "==========================================\n\n";

?>

