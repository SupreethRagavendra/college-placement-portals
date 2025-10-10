<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StudentResult;

$result = StudentResult::where('assessment_id', 29)
    ->orderBy('submitted_at', 'desc')
    ->first();

if (!$result) {
    echo "No results found\n";
    exit;
}

echo "Raw JSON from database:\n";
echo $result->getAttributes()['answers'] . "\n\n";

echo "Decoded array:\n";
$answers = json_decode($result->getAttributes()['answers'], true);
print_r($answers);

echo "\nEach answer:\n";
foreach ($answers as $qId => $ans) {
    echo "Question {$qId}: '{$ans}' (type: " . gettype($ans) . ", value: " . var_export($ans, true) . ")\n";
}
