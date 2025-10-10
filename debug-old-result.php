<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check the old result that has 0/2
$result = \App\Models\StudentResult::find(23);

if ($result) {
    echo "Result ID: 23\n";
    echo "Score: " . $result->score . " / " . $result->total_questions . "\n";
    echo "Submitted at: " . $result->submitted_at . "\n";
    echo "\nAnswers stored:\n";
    print_r($result->answers);
    
    echo "\n\nChecking if answers would be correct:\n";
    foreach ($result->answers as $questionId => $answer) {
        $question = \App\Models\Question::find($questionId);
        if ($question) {
            $isCorrect = $question->isCorrectAnswer($answer);
            echo "Question $questionId: Student answered '$answer' (type: " . gettype($answer) . ") - Correct: " . ($isCorrect ? 'YES' : 'NO') . "\n";
            echo "  correct_option: " . ($question->getRawOriginal('correct_option') ?? 'NULL') . "\n";
            echo "  correct_answer: " . ($question->getRawOriginal('correct_answer') ?? 'NULL') . "\n";
        }
    }
}

