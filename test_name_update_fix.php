<?php
/**
 * Test Name Update Fix
 * 
 * This script tests the RAG name update functionality to ensure:
 * 1. Laravel sends student info to RAG
 * 2. RAG service can extract names properly
 * 3. Database updates are working
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

echo "========================================\n";
echo "  RAG NAME UPDATE FIX - VERIFICATION\n";
echo "========================================\n\n";

// Test 1: Check RAG Service is Running
echo "TEST 1: Checking RAG Service Health...\n";
try {
    $response = Http::timeout(5)->get('http://localhost:8001/health');
    if ($response->successful()) {
        echo "✅ RAG service is running\n";
        $data = $response->json();
        echo "   Status: " . $data['status'] . "\n";
        echo "   Database: " . ($data['database'] ?? 'unknown') . "\n\n";
    } else {
        echo "❌ RAG service returned error: " . $response->status() . "\n\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ RAG service is not running!\n";
    echo "   Error: " . $e->getMessage() . "\n";
    echo "   Please start RAG service first: python python-rag/main.py\n\n";
    exit(1);
}

// Test 2: Get a test student
echo "TEST 2: Finding test student...\n";
$student = DB::table('users')
    ->where('role', 'student')
    ->first();

if (!$student) {
    echo "❌ No student found in database\n\n";
    exit(1);
}

echo "✅ Test student found\n";
echo "   ID: {$student->id}\n";
echo "   Current Name: {$student->name}\n";
echo "   Email: {$student->email}\n\n";

// Test 3: Test Name Change Request
echo "TEST 3: Testing name change request...\n";
$testName = "Test Name " . date('His'); // Unique name with timestamp
$testMessage = "Change my name to {$testName}";

echo "   Sending request: '{$testMessage}'\n";

try {
    $response = Http::timeout(10)->post('http://localhost:8001/chat', [
        'student_id' => $student->id,
        'message' => $testMessage,
        'student_name' => $student->name,
        'student_email' => $student->email
    ]);
    
    if ($response->successful()) {
        $data = $response->json();
        
        echo "\n   Response received:\n";
        echo "   - Query Type: " . ($data['query_type'] ?? 'unknown') . "\n";
        echo "   - Message: " . substr($data['message'], 0, 100) . "...\n";
        echo "   - Has Special Action: " . (isset($data['special_action']) ? 'Yes' : 'No') . "\n";
        
        if (isset($data['special_action'])) {
            echo "\n   ✅ Special Action Detected!\n";
            echo "   - Type: " . $data['special_action']['type'] . "\n";
            echo "   - New Name: " . $data['special_action']['new_name'] . "\n";
            
            if ($data['special_action']['new_name'] === $testName) {
                echo "   ✅ Name extracted correctly!\n\n";
            } else {
                echo "   ❌ Name mismatch!\n";
                echo "      Expected: {$testName}\n";
                echo "      Got: " . $data['special_action']['new_name'] . "\n\n";
            }
        } else {
            echo "\n   ❌ No special action in response!\n";
            echo "   This means the regex pattern didn't match.\n\n";
            echo "   Full response:\n";
            echo "   " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
        }
    } else {
        echo "❌ RAG service returned error: " . $response->status() . "\n";
        echo "   Response: " . $response->body() . "\n\n";
    }
} catch (Exception $e) {
    echo "❌ Request failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 4: Test Different Name Formats
echo "TEST 4: Testing different name formats...\n\n";

$testCases = [
    'Supreeth Ragavendra S',
    'John Smith',
    'Alex',
    'Michael Angelo Buonarroti Junior',
    'María García López',
    'O\'Brien Patrick'
];

foreach ($testCases as $index => $testName) {
    echo "   Test " . ($index + 1) . ": '{$testName}'\n";
    
    try {
        $response = Http::timeout(10)->post('http://localhost:8001/chat', [
            'student_id' => $student->id,
            'message' => "Change my name to {$testName}",
            'student_name' => $student->name,
            'student_email' => $student->email
        ]);
        
        if ($response->successful()) {
            $data = $response->json();
            
            if (isset($data['special_action'])) {
                $extractedName = $data['special_action']['new_name'];
                
                if ($extractedName === $testName) {
                    echo "      ✅ Extracted correctly: '{$extractedName}'\n";
                } else {
                    echo "      ⚠️  Partial match:\n";
                    echo "         Expected: '{$testName}'\n";
                    echo "         Got: '{$extractedName}'\n";
                }
            } else {
                echo "      ❌ Failed to extract name\n";
            }
        } else {
            echo "      ❌ Request failed: " . $response->status() . "\n";
        }
    } catch (Exception $e) {
        echo "      ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Summary
echo "========================================\n";
echo "  TEST SUMMARY\n";
echo "========================================\n\n";

echo "NEXT STEPS:\n";
echo "1. If all tests passed ✅:\n";
echo "   - The fix is working!\n";
echo "   - Test in the actual chatbot UI\n";
echo "   - Try: 'Change my name to Supreeth Ragavendra S'\n\n";

echo "2. If tests failed ❌:\n";
echo "   - Check RAG service logs: python-rag/rag_service.log\n";
echo "   - Check Laravel logs: storage/logs/laravel.log\n";
echo "   - Make sure RAG service is restarted after code changes\n\n";

echo "3. To test in UI:\n";
echo "   - Login as student\n";
echo "   - Open chatbot\n";
echo "   - Type: 'Change my name to [Your Name]'\n";
echo "   - Verify name updates in profile\n\n";

echo "========================================\n";

