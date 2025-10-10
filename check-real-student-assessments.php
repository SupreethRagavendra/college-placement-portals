<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\StudentAssessment;

echo "\n==========================================\n";
echo "CHECKING REAL STUDENT ASSESSMENTS\n";
echo "==========================================\n\n";

// Find user (search by name containing Supreeth)
$user = User::where('name', 'LIKE', '%Supreeth%')
    ->orWhere('email', 'LIKE', '%supreeth%')
    ->first();

if (!$user) {
    echo "❌ User not found with name containing 'Supreeth'\n";
    echo "Checking all users with role=student:\n\n";
    
    $students = User::where('role', 'student')->get();
    foreach ($students as $student) {
        echo "- ID: {$student->id} | Name: {$student->name} | Email: {$student->email}\n";
    }
    exit;
}

echo "✅ Found user: {$user->name}\n";
echo "   ID: {$user->id}\n";
echo "   Email: {$user->email}\n";
echo "   Role: {$user->role}\n\n";

// Get ALL assessments for this user
$assessments = StudentAssessment::where('student_id', $user->id)
    ->with('assessment')
    ->orderBy('created_at', 'desc')
    ->get();

echo "==========================================\n";
echo "ASSESSMENT HISTORY\n";
echo "==========================================\n\n";

if ($assessments->isEmpty()) {
    echo "❌ No assessments found for this user!\n\n";
    echo "Checking if there are ANY student assessments in the database...\n";
    
    $anyAssessments = StudentAssessment::with('student', 'assessment')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    if ($anyAssessments->isEmpty()) {
        echo "❌ No student assessments exist in the entire database!\n";
    } else {
        echo "Found {$anyAssessments->count()} assessment(s) from other students:\n\n";
        foreach ($anyAssessments as $a) {
            $studentName = $a->student->name ?? 'Unknown';
            $assessmentName = $a->assessment->name ?? 'Unknown';
            echo "- Student: {$studentName} (ID: {$a->student_id})\n";
            echo "  Assessment: {$assessmentName}\n";
            echo "  Status: {$a->status}\n";
            echo "  Score: {$a->obtained_marks}/{$a->total_marks} ({$a->percentage}%)\n";
            echo "  ---\n";
        }
    }
} else {
    echo "✅ Found {$assessments->count()} assessment attempt(s):\n\n";
    
    $completedCount = 0;
    $inProgressCount = 0;
    
    foreach ($assessments as $a) {
        $assessmentName = $a->assessment->name ?? 'Unknown Assessment';
        $status = strtoupper($a->status);
        $statusIcon = $a->status === 'completed' ? '✅' : '🔄';
        
        echo "{$statusIcon} {$assessmentName}\n";
        echo "   Status: {$status}\n";
        echo "   Score: {$a->obtained_marks}/{$a->total_marks} ({$a->percentage}%)\n";
        echo "   Pass Status: {$a->pass_status}\n";
        echo "   Started: {$a->start_time}\n";
        
        if ($a->submit_time) {
            echo "   Submitted: {$a->submit_time}\n";
        }
        
        echo "   ---\n";
        
        if ($a->status === 'completed') {
            $completedCount++;
        } elseif ($a->status === 'in_progress') {
            $inProgressCount++;
        }
    }
    
    echo "\n==========================================\n";
    echo "SUMMARY\n";
    echo "==========================================\n";
    echo "Total Attempts: {$assessments->count()}\n";
    echo "Completed: {$completedCount}\n";
    echo "In Progress: {$inProgressCount}\n";
    
    if ($completedCount > 0) {
        $avgScore = $assessments->where('status', 'completed')->avg('percentage');
        $passedCount = $assessments->where('pass_status', 'pass')->count();
        echo "Average Score: " . round($avgScore, 1) . "%\n";
        echo "Passed: {$passedCount}\n";
    }
}

echo "\n==========================================\n";
echo "DIAGNOSIS\n";
echo "==========================================\n\n";

if ($assessments->count() > 0) {
    echo "✅ User HAS assessment data!\n\n";
    echo "If RAG says 'no data', the issue is:\n";
    echo "1. Wrong student ID being used\n";
    echo "2. Context not being passed correctly\n";
    echo "3. Browser cache issue\n\n";
    
    echo "SOLUTION:\n";
    echo "1. Clear Laravel cache: php artisan cache:clear\n";
    echo "2. Clear browser cache (Ctrl+Shift+Delete)\n";
    echo "3. Check logged in user ID matches: {$user->id}\n";
} else {
    echo "❌ User has NO assessment data in database\n\n";
    echo "This explains why RAG can't show results.\n";
}

echo "\n==========================================\n\n";

?>

