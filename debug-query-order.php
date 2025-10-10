<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate what happens in the result method
$studentId = 49; // The student from the screenshot
$assessmentId = 29; // The assessment from the URL

echo "Getting result the way the controller does (first()):\n";
$result1 = \App\Models\StudentResult::where('student_id', $studentId)
    ->where('assessment_id', $assessmentId)
    ->first();
    
if ($result1) {
    echo "Result ID: " . $result1->id . "\n";
    echo "Score: " . $result1->score . " / " . $result1->total_questions . "\n";
    echo "Submitted at: " . $result1->submitted_at . "\n";
}

echo "\n\nGetting result ordered by latest (orderBy desc + first()):\n";
$result2 = \App\Models\StudentResult::where('student_id', $studentId)
    ->where('assessment_id', $assessmentId)
    ->orderBy('id', 'desc')
    ->first();
    
if ($result2) {
    echo "Result ID: " . $result2->id . "\n";
    echo "Score: " . $result2->score . " / " . $result2->total_questions . "\n";
    echo "Submitted at: " . $result2->submitted_at . "\n";
}

echo "\n\nAll results for this student+assessment:\n";
$allResults = \App\Models\StudentResult::where('student_id', $studentId)
    ->where('assessment_id', $assessmentId)
    ->orderBy('id', 'asc')
    ->get();
    
foreach ($allResults as $r) {
    echo "ID: " . $r->id . " - Score: " . $r->score . "/" . $r->total_questions . " - " . $r->submitted_at . "\n";
}

