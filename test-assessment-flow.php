<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Assessment;
use App\Models\Question;

$assessment = Assessment::find(29);
if (!$assessment) {
    echo "Assessment not found\n";
    exit;
}

echo "TESTING ASSESSMENT FLOW\n";
echo "========================\n\n";

$questions = $assessment->questions()->where('is_active', true)->get();

foreach ($questions as $q) {
    echo "Question #{$q->id}: " . substr($q->question_text, 0, 50) . "...\n";
    
    // Get options using the accessor
    $options = $q->options;
    
    echo "Options array from accessor:\n";
    foreach ($options as $index => $text) {
        $letter = chr(65 + $index);
        echo "  Index {$index} → Letter {$letter}: {$text}\n";
    }
    
    echo "Correct answer: '{$q->correct_answer}' (option index: {$q->correct_option})\n";
    
    // Test each letter
    echo "Testing isCorrectAnswer():\n";
    foreach (['A', 'B', 'C', 'D'] as $letter) {
        $isCorrect = $q->isCorrectAnswer($letter);
        echo "  {$letter}: " . ($isCorrect ? '✓ CORRECT' : '✗ wrong') . "\n";
    }
    echo "\n";
}

echo "INDEX MAPPING TEST:\n";
echo "===================\n";
echo "The system should map:\n";
echo "  Index 0 → Letter A → option_a\n";
echo "  Index 1 → Letter B → option_b\n";
echo "  Index 2 → Letter C → option_c\n";
echo "  Index 3 → Letter D → option_d\n";
echo "\nThis mapping MUST be consistent across:\n";
echo "  1. Form submission (take.blade.php)\n";
echo "  2. Question model accessor\n";
echo "  3. Answer validation\n";
echo "  4. Result display\n";
