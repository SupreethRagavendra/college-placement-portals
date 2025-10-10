<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Assessment;

$assessment = Assessment::find(58);

if ($assessment) {
    echo "Assessment Title: " . $assessment->title . "\n";
    echo "Assessment ID: " . $assessment->id . "\n";
    echo "Total Questions: " . $assessment->questions()->count() . "\n";
    echo "Active Questions: " . $assessment->questions()->where('is_active', true)->count() . "\n";
    
    echo "\nQuestions List:\n";
    foreach($assessment->questions as $question) {
        echo "  - Question ID " . $question->id . ": " . substr($question->question_text, 0, 50) . "...\n";
    }
} else {
    echo "Assessment with ID 58 not found.\n";
}
