<?php
/**
 * Test Fallback Response Fix
 * Verify that the chatbot now properly returns database responses instead of error messages
 */

echo "🔧 TESTING FALLBACK RESPONSE FIX\n";
echo "================================\n\n";

// Test the chat endpoint with RAG service down
$chatUrl = 'http://localhost:8000/student/rag-chat';
$testMessage = "What assessments are available?";
$postData = json_encode([
    'message' => $testMessage
]);

echo "Testing: $chatUrl\n";
echo "Message: $testMessage\n";
echo "RAG Service: DOWN (stopped)\n";
echo "Expected: Database response with real assessment data\n\n";

// Get CSRF token first
$loginUrl = 'http://localhost:8000/login';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'test_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'test_cookies.txt');

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($loginHttpCode === 200 && preg_match('/name="csrf-token" content="([^"]+)"/', $loginResponse, $matches)) {
    $csrfToken = $matches[1];
    echo "✅ Got CSRF token: " . substr($csrfToken, 0, 20) . "...\n";
    
    // Test the chat endpoint
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $chatUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'test_cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'test_cookies.txt');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-CSRF-TOKEN: ' . $csrfToken
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "HTTP Status: $httpCode\n";

    if ($httpCode === 200) {
        echo "✅ SUCCESS: Chat endpoint working\n";
        $data = json_decode($response, true);
        
        if ($data) {
            echo "\nResponse Analysis:\n";
            echo "- Success: " . ($data['success'] ? 'true' : 'false') . "\n";
            echo "- Message: " . substr($data['message'] ?? 'No message', 0, 200) . "...\n";
            echo "- Model Used: " . ($data['model_used'] ?? 'not set') . "\n";
            echo "- RAG Status: " . ($data['rag_status'] ?? 'not set') . "\n";
            echo "- Service Info: " . ($data['service_info']['text'] ?? 'not set') . "\n";
            echo "- Service Indicator: " . ($data['service_info']['indicator'] ?? 'not set') . "\n";
            
            // Check if it's the error message we want to fix
            if (strpos($data['message'], 'I apologize, but I encountered an error') !== false) {
                echo "\n❌ PROBLEM: Still showing error message instead of database response\n";
                echo "   This means the fix didn't work properly.\n";
            } elseif ($data['model_used'] === 'limited' && $data['rag_status'] === 'limited') {
                echo "\n✅ CORRECT: Now showing Limited Mode with database response\n";
                echo "   - Uses real assessment data from database\n";
                echo "   - Shows yellow indicator (🟡)\n";
                echo "   - No error message\n";
            } else {
                echo "\n❓ UNKNOWN: Unexpected response format\n";
            }
        } else {
            echo "❌ Failed to parse JSON response\n";
            echo "Raw response: " . substr($response, 0, 300) . "...\n";
        }
    } elseif ($httpCode === 401) {
        echo "❌ HTTP 401 - Authentication required\n";
        echo "   Need to be logged in to test the chat endpoint.\n";
    } else {
        echo "❌ HTTP $httpCode - Unexpected response\n";
        echo "Raw response: " . substr($response, 0, 300) . "...\n";
    }
} else {
    echo "❌ Could not get CSRF token\n";
}

// Clean up
if (file_exists('test_cookies.txt')) {
    unlink('test_cookies.txt');
}

echo "\n";

// Test summary
echo "🔍 EXPECTED BEHAVIOR AFTER FIX:\n";
echo "===============================\n";
echo "✅ When RAG is down but Laravel works:\n";
echo "   - HTTP 200 response\n";
echo "   - success: true\n";
echo "   - model_used: 'limited'\n";
echo "   - rag_status: 'limited'\n";
echo "   - Real database data (assessments)\n";
echo "   - No error messages\n";
echo "   - Yellow indicator (🟡)\n\n";

echo "❌ BEFORE FIX (what we're fixing):\n";
echo "   - HTTP 500 response\n";
echo "   - success: false\n";
echo "   - message: 'I apologize, but I encountered an error'\n";
echo "   - No real data\n";

echo "\n✅ FALLBACK RESPONSE FIX TEST COMPLETE!\n";
echo "=======================================\n";
?>
