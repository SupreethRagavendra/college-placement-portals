<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Question;

// Sample questions with options
$questionsData = [
    2 => [
        'question_text' => 'What is the size of int variable in Java?',
        'option_a' => '16 bit',
        'option_b' => '32 bit',
        'option_c' => '64 bit',
        'option_d' => '8 bit',
        'correct_answer' => 'B', // 32 bit
        'options' => ['16 bit', '32 bit', '64 bit', '8 bit'],
    ],
    3 => [
        'question_text' => 'Which keyword is used to inherit a class in Java?',
        'option_a' => 'implements',
        'option_b' => 'extends',
        'option_c' => 'inherits',
        'option_d' => 'super',
        'correct_answer' => 'B', // extends
        'options' => ['implements', 'extends', 'inherits', 'super'],
    ],
    4 => [
        'question_text' => 'What is method overloading?',
        'option_a' => 'Same method name with different parameters',
        'option_b' => 'Different method names with same parameters',
        'option_c' => 'Same method name and same parameters',
        'option_d' => 'None of the above',
        'correct_answer' => 'A', // Same method name with different parameters
        'options' => [
            'Same method name with different parameters',
            'Different method names with same parameters',
            'Same method name and same parameters',
            'None of the above'
        ],
    ],
    5 => [
        'question_text' => 'Which collection class allows you to access its elements by associating a key with an element value?',
        'option_a' => 'ArrayList',
        'option_b' => 'HashSet',
        'option_c' => 'HashMap',
        'option_d' => 'LinkedList',
        'correct_answer' => 'C', // HashMap
        'options' => ['ArrayList', 'HashSet', 'HashMap', 'LinkedList'],
    ],
];

foreach ($questionsData as $questionId => $data) {
    $question = Question::find($questionId);
    
    if ($question) {
        // Convert letter to index
        $correctAnswerLetter = $data['correct_answer'];
        $correctAnswerIndex = ord($correctAnswerLetter) - ord('A'); // A=0, B=1, C=2, D=3
        
        $question->update([
            'question_text' => $data['question_text'],
            'question' => $data['question_text'], // Also update 'question' field
            'option_a' => $data['option_a'],
            'option_b' => $data['option_b'],
            'option_c' => $data['option_c'],
            'option_d' => $data['option_d'],
            'options' => json_encode($data['options']),
            'correct_answer' => $correctAnswerLetter,
            'correct_option' => $correctAnswerIndex,
            'question_type' => 'mcq',
        ]);
        
        echo "✓ Updated Question #{$questionId}: {$data['question_text']}\n";
        echo "  Options added: A, B, C, D\n";
        echo "  Correct Answer: {$correctAnswerLetter}\n\n";
    } else {
        echo "✗ Question #{$questionId} not found\n\n";
    }
}

echo "\n✅ All questions updated successfully!\n";
echo "You can now test the assessment in the student panel.\n";
