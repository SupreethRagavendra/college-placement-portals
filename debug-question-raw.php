<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get question by ID and check raw database data
$question = \App\Models\Question::find(15);

if ($question) {
    echo "Question ID: 15\n";
    echo "Raw attributes:\n";
    print_r($question->attributes);
    
    echo "\n\ngetRawOriginal data:\n";
    foreach (['correct_option', 'correct_answer', 'options', 'option_a', 'option_b', 'option_c', 'option_d'] as $field) {
        echo "$field: " . var_export($question->getRawOriginal($field), true) . "\n";
    }
}

