<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Assessment;
use App\Models\StudentAssessment;

echo "=== ACTIVE ASSESSMENTS IN DATABASE ===\n\n";

$assessments = Assessment::where('is_active', true)
    ->orderBy('id')
    ->get(['id', 'name', 'category', 'total_time']);

foreach ($assessments as $assessment) {
    echo "ID: {$assessment->id}\n";
    echo "Name: {$assessment->name}\n";
    echo "Category: {$assessment->category}\n";
    echo "Duration: {$assessment->total_time} minutes\n";
    echo "---\n";
}

echo "\n=== STUDENT 52 ASSESSMENTS ===\n\n";

$studentAssessments = StudentAssessment::where('student_id', 52)
    ->with('assessment:id,name')
    ->get();

if ($studentAssessments->count() > 0) {
    echo "Student 52 has taken:\n";
    foreach ($studentAssessments as $sa) {
        echo "- {$sa->assessment->name} (Assessment ID: {$sa->assessment_id})\n";
    }
} else {
    echo "Student 52 has NOT taken any assessments yet.\n";
}

echo "\n=== AVAILABLE FOR STUDENT 52 ===\n\n";

$takenIds = StudentAssessment::where('student_id', 52)->pluck('assessment_id')->toArray();
$available = Assessment::where('is_active', true)
    ->whereNotIn('id', $takenIds)
    ->get(['id', 'name', 'category']);

if ($available->count() > 0) {
    echo "Available assessments for student 52:\n";
    foreach ($available as $a) {
        echo "- {$a->name} ({$a->category})\n";
    }
} else {
    echo "NO assessments available (all taken or none active)\n";
}

