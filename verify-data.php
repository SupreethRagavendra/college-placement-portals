<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== VERIFICATION ===\n\n";
    
    // Check assessments
    $assessments = DB::table('assessments')->get();
    echo "Assessments created: " . count($assessments) . "\n";
    foreach ($assessments as $assessment) {
        echo "- ID: {$assessment->id}, Name: {$assessment->name}, Category: {$assessment->category}\n";
    }
    
    // Check questions
    $questions = DB::table('questions')->get();
    echo "\nQuestions created: " . count($questions) . "\n";
    
    // Check assessment-question links
    $links = DB::table('assessment_questions')->get();
    echo "Assessment-Question links: " . count($links) . "\n";
    
    echo "\n=== CURRENT STATUS ===\n";
    echo "✅ Assessments: Successfully created\n";
    echo "❓ Questions: Need to check why creation failed\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
