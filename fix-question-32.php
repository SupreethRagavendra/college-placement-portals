<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Question;

// Fix Question #32 with proper Java access modifier options
$question = Question::find(32);

if ($question) {
    echo "Updating Question #32...\n";
    echo "Current question: {$question->question_text}\n";
    echo "Current options: " . json_encode($question->options) . "\n";
    echo "Current correct answer: {$question->correct_answer}\n\n";
    
    // Update with proper Java access modifier options
    $question->update([
        'question_text' => 'Which of the following is not a valid access modifier in Java?',
        'question' => 'Which of the following is not a valid access modifier in Java?',
        'option_a' => 'public',
        'option_b' => 'private',
        'option_c' => 'protected',
        'option_d' => 'friend',
        'options' => json_encode(['public', 'private', 'protected', 'friend']),
        'correct_answer' => 'D',
        'correct_option' => 3, // D = index 3
        'question_type' => 'mcq',
    ]);
    
    echo "✓ Updated Question #32 successfully!\n";
    echo "New question: {$question->fresh()->question_text}\n";
    echo "New options:\n";
    $newOptions = $question->fresh()->options;
    foreach ($newOptions as $i => $opt) {
        $letter = chr(65 + $i);
        $isCorrect = $letter === 'D' ? ' ← CORRECT' : '';
        echo "  {$letter}. {$opt}{$isCorrect}\n";
    }
    echo "\nCorrect answer: D (friend is not a valid Java access modifier)\n";
    
} else {
    echo "Question #32 not found!\n";
}
