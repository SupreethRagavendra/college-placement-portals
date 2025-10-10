<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the most recent result
$result = \App\Models\StudentResult::orderBy('id', 'desc')->first();

if ($result) {
    echo "Result ID: " . $result->id . "\n";
    echo "Score: " . $result->score . " / " . $result->total_questions . "\n";
    echo "Assessment ID: " . $result->assessment_id . "\n";
    echo "Student ID: " . $result->student_id . "\n";
    echo "\nAnswers (raw):\n";
    echo "Type: " . gettype($result->answers) . "\n";
    print_r($result->answers);
    
    echo "\n\nAnswers from database:\n";
    print_r($result->getRawOriginal('answers'));
    
    echo "\n\nLet's check the questions and validate:\n";
    $answers = is_array($result->answers) ? $result->answers : json_decode($result->answers, true) ?? [];
    
    foreach ($answers as $questionId => $answer) {
        $question = \App\Models\Question::find($questionId);
        if ($question) {
            $isCorrect = $question->isCorrectAnswer($answer);
            echo "Question $questionId: Student answered '$answer' - Correct: " . ($isCorrect ? 'YES' : 'NO') . "\n";
            echo "  Question text: " . substr($question->question ?? $question->question_text ?? '', 0, 50) . "...\n";
            echo "  Correct option from DB: " . ($question->getRawOriginal('correct_option') ?? 'NULL') . "\n";
            echo "  Correct answer from DB: " . ($question->getRawOriginal('correct_answer') ?? 'NULL') . "\n";
        }
    }
} else {
    echo "No results found!\n";
}

