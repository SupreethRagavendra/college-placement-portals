<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Question;
use App\Models\Assessment;

// Get assessment ID from command line or use default
$assessmentId = $argv[1] ?? 29;

echo "Checking questions for Assessment ID: {$assessmentId}\n";
echo "==============================================================================\n\n";

$assessment = Assessment::find($assessmentId);
if (!$assessment) {
    echo "Assessment not found!\n";
    exit(1);
}

echo "Assessment: {$assessment->title}\n";
echo "Allow Multiple Attempts: " . ($assessment->allow_multiple_attempts ? 'Yes' : 'No') . "\n\n";

$questions = $assessment->questions()->get();

if ($questions->isEmpty()) {
    echo "No questions found for this assessment!\n";
    exit(0);
}

foreach ($questions as $index => $question) {
    echo "------------------------------------------------------------------------------\n";
    echo "Question #{$question->id}:\n";
    echo "  Text: {$question->question_text}\n";
    echo "  Type: {$question->question_type}\n";
    echo "  correct_answer: '{$question->correct_answer}'\n";
    echo "  correct_option: {$question->correct_option}\n";
    echo "\n";
    
    // Get options using the accessor
    $options = $question->options;
    echo "  Options from accessor:\n";
    if (is_array($options) && !empty($options)) {
        foreach ($options as $i => $opt) {
            $letter = chr(65 + $i);
            echo "    [{$letter}] {$opt}\n";
        }
    } else {
        echo "    NO OPTIONS FOUND IN ACCESSOR\n";
    }
    echo "\n";
    
    // Also check raw columns
    echo "  Raw option columns:\n";
    echo "    option_a: '{$question->option_a}'\n";
    echo "    option_b: '{$question->option_b}'\n";
    echo "    option_c: '{$question->option_c}'\n";
    echo "    option_d: '{$question->option_d}'\n";
    echo "\n";
    
    // Check JSON options field
    echo "  Raw options JSON field: " . var_export($question->getAttributes()['options'] ?? null, true) . "\n\n";
    
    // Test the isCorrectAnswer method
    echo "  Testing answers:\n";
    foreach (['A', 'B', 'C', 'D'] as $testAnswer) {
        $isCorrect = $question->isCorrectAnswer($testAnswer);
        $icon = $isCorrect ? '[CORRECT]' : '[wrong]';
        echo "    Answer {$testAnswer}: {$icon}\n";
    }
    
    echo "\n";
}

echo "==============================================================================\n";
echo "Total questions: {$questions->count()}\n";
