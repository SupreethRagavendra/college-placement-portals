<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find the question about int size
$question = \App\Models\Question::where('question', 'LIKE', '%size of int%')->first();

if (!$question) {
    $question = \App\Models\Question::where('question_text', 'LIKE', '%size of int%')->first();
}

if ($question) {
    echo "Found question ID: " . $question->id . "\n";
    echo "Question: " . ($question->question ?? $question->question_text) . "\n";
    echo "correct_option (accessor): " . var_export($question->correct_option, true) . "\n";
    echo "correct_answer (accessor): " . var_export($question->correct_answer, true) . "\n";
    echo "attributes['correct_option']: " . var_export($question->attributes['correct_option'] ?? 'NOT SET', true) . "\n";
    echo "attributes['correct_answer']: " . var_export($question->attributes['correct_answer'] ?? 'NOT SET', true) . "\n";
    echo "\nOptions: \n";
    echo "option_a: " . ($question->option_a ?? 'NOT SET') . "\n";
    echo "option_b: " . ($question->option_b ?? 'NOT SET') . "\n";
    echo "option_c: " . ($question->option_c ?? 'NOT SET') . "\n";
    echo "option_d: " . ($question->option_d ?? 'NOT SET') . "\n";
    
    echo "\nTesting isCorrectAnswer method:\n";
    echo "isCorrectAnswer('A'): " . var_export($question->isCorrectAnswer('A'), true) . "\n";
    echo "isCorrectAnswer('B'): " . var_export($question->isCorrectAnswer('B'), true) . "\n";
    echo "isCorrectAnswer('C'): " . var_export($question->isCorrectAnswer('C'), true) . "\n";
    echo "isCorrectAnswer('D'): " . var_export($question->isCorrectAnswer('D'), true) . "\n";
} else {
    echo "Question not found!\n";
    echo "Let's check all questions:\n";
    $questions = \App\Models\Question::take(5)->get();
    foreach ($questions as $q) {
        echo "ID: " . $q->id . " - " . substr($q->question ?? $q->question_text ?? 'NO QUESTION', 0, 50) . "...\n";
    }
}

