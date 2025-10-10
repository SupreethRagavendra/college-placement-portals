<?php
// Simple test script to verify Laravel can communicate with RAG service

echo "Testing Laravel RAG service communication...\n";
echo str_repeat("=", 50) . "\n";

// Test health endpoint
$healthUrl = 'http://localhost:8001/health';
echo "Testing health endpoint: $healthUrl\n";

$healthContext = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10,
    ]
]);

$healthResult = @file_get_contents($healthUrl, false, $healthContext);
if ($healthResult !== false) {
    echo "✓ Health check successful\n";
    $healthData = json_decode($healthResult, true);
    if ($healthData) {
        echo "  Status: " . ($healthData['status'] ?? 'Unknown') . "\n";
        echo "  Database: " . ($healthData['database'] ?? 'Unknown') . "\n";
        echo "  Primary Model: " . ($healthData['primary_model'] ?? 'Unknown') . "\n";
    }
} else {
    echo "✗ Health check failed\n";
    echo "  Error: " . error_get_last()['message'] ?? 'Unknown error' . "\n";
}

echo "\n";

// Test chat endpoint with valid student data
$chatUrl = 'http://localhost:8001/chat';
echo "Testing chat endpoint: $chatUrl\n";

$chatData = [
    'student_id' => 11,
    'message' => 'What assessments are available?',
    'student_email' => 'admin@portal.com',
    'student_name' => 'Admin User'
];

$chatContext = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
        ],
        'content' => json_encode($chatData),
        'timeout' => 30,
    ]
]);

$chatResult = @file_get_contents($chatUrl, false, $chatContext);
if ($chatResult !== false) {
    echo "✓ Chat endpoint successful\n";
    $chatResponse = json_decode($chatResult, true);
    if ($chatResponse && isset($chatResponse['success']) && $chatResponse['success']) {
        echo "  Response: " . substr($chatResponse['message'] ?? 'No message', 0, 100) . "...\n";
    } else {
        echo "  Error response: " . json_encode($chatResponse) . "\n";
    }
} else {
    echo "✗ Chat endpoint failed\n";
    echo "  Error: " . error_get_last()['message'] ?? 'Unknown error' . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test completed!\n";