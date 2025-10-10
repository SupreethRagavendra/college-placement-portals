<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the most recent 10 results
$results = \App\Models\StudentResult::orderBy('id', 'desc')->take(10)->get();

echo "Recent results:\n\n";

foreach ($results as $result) {
    echo "Result ID: " . $result->id . "\n";
    echo "Score: " . $result->score . " / " . $result->total_questions . "\n";
    echo "Assessment ID: " . $result->assessment_id . "\n";
    echo "Student ID: " . ($result->student_id ?? 'NULL') . "\n";
    echo "Submitted at: " . ($result->submitted_at ?? 'NULL') . "\n";
    
    $answers = is_array($result->answers) ? $result->answers : json_decode($result->answers, true) ?? [];
    echo "Answers: " . json_encode($answers) . "\n";
    
    // Check if this result has the issue (0 score but has answers)
    if ($result->score == 0 && !empty($answers)) {
        echo "*** FOUND RESULT WITH 0 SCORE BUT HAS ANSWERS! ***\n";
        foreach ($answers as $questionId => $answer) {
            $question = \App\Models\Question::find($questionId);
            if ($question) {
                $isCorrect = $question->isCorrectAnswer($answer);
                echo "  Question $questionId: Student answered '$answer' - Should be correct: " . ($isCorrect ? 'YES' : 'NO') . "\n";
            }
        }
    }
    
    echo "\n" . str_repeat('-', 80) . "\n\n";
}

