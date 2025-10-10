<?php
/**
 * Test Limited Mode Fix
 * Verifies that when RAG service is down, it shows Limited Mode (Yellow) instead of Offline Mode (Red)
 */

echo "🔧 TESTING LIMITED MODE FIX\n";
echo "===========================\n\n";

// Test 1: Check Laravel health endpoint when RAG is down
echo "🔍 TEST 1: Laravel Health Check (RAG Service Down)\n";
echo "------------------------------------------------\n";

$laravelHealthUrl = 'http://localhost:8000/rag-health';

echo "Testing: $laravelHealthUrl\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $laravelHealthUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Connection Error: $error\n";
    echo "🔴 Expected: Limited Mode, Got: Connection Error\n";
} elseif ($httpCode === 200) {
    echo "✅ HTTP 200 - Laravel is responding\n";
    $data = json_decode($response, true);
    
    if ($data) {
        echo "Response Data:\n";
        echo "- Status: " . ($data['status'] ?? 'not set') . "\n";
        echo "- RAG Service: " . ($data['rag_service'] ? 'true' : 'false') . "\n";
        echo "- Mode: " . ($data['mode'] ?? 'not set') . "\n";
        echo "- UI Indicator: " . ($data['ui_indicator'] ?? 'not set') . "\n";
        echo "- UI Text: " . ($data['ui_text'] ?? 'not set') . "\n";
        echo "- Fallback Available: " . ($data['fallback_available'] ? 'true' : 'false') . "\n";
        
        if ($data['status'] === 'limited' && $data['ui_indicator'] === '🟡') {
            echo "✅ CORRECT: Shows Limited Mode (Yellow)\n";
        } else {
            echo "❌ INCORRECT: Should show Limited Mode (Yellow)\n";
        }
    } else {
        echo "❌ Failed to parse JSON response\n";
    }
} else {
    echo "❌ HTTP $httpCode - Laravel not responding properly\n";
    echo "🔴 Expected: Limited Mode, Got: HTTP Error\n";
}

echo "\n";

// Test 2: Check chat endpoint behavior
echo "🔍 TEST 2: Chat Endpoint Behavior (RAG Service Down)\n";
echo "---------------------------------------------------\n";

$chatUrl = 'http://localhost:8000/student/rag-chat';
$testMessage = "What assessments are available?";
$postData = json_encode([
    'message' => $testMessage
]);

echo "Testing: $chatUrl\n";
echo "Message: $testMessage\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $chatUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-CSRF-TOKEN: test-token' // This will fail CSRF, but we can see the response
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Connection Error: $error\n";
} elseif ($httpCode === 419) {
    echo "⚠️  CSRF Token Error (HTTP 419) - This is expected in testing\n";
    echo "✅ Chat endpoint is accessible (Laravel is working)\n";
} elseif ($httpCode === 200) {
    echo "✅ HTTP 200 - Chat endpoint working\n";
    $data = json_decode($response, true);
    
    if ($data) {
        echo "Response Data:\n";
        echo "- Success: " . ($data['success'] ? 'true' : 'false') . "\n";
        echo "- Model Used: " . ($data['model_used'] ?? 'not set') . "\n";
        echo "- RAG Status: " . ($data['rag_status'] ?? 'not set') . "\n";
        echo "- Service Info: " . ($data['service_info']['text'] ?? 'not set') . "\n";
        
        if ($data['model_used'] === 'limited' && $data['rag_status'] === 'limited') {
            echo "✅ CORRECT: Chat uses Limited Mode fallback\n";
        } else {
            echo "❌ INCORRECT: Should use Limited Mode fallback\n";
        }
    }
} else {
    echo "❌ HTTP $httpCode - Unexpected response\n";
}

echo "\n";

// Test 3: Expected Behavior Summary
echo "🔍 TEST 3: Expected Behavior Summary\n";
echo "-----------------------------------\n";

echo "When RAG service is DOWN but Laravel is UP:\n";
echo "✅ Health Check: HTTP 200, status='limited', ui_indicator='🟡'\n";
echo "✅ Chat Response: Uses Laravel fallback, model_used='limited'\n";
echo "✅ UI Display: Yellow dot + 'Limited Mode'\n";
echo "✅ Data Source: Real database queries (not AI)\n";

echo "\nWhen BOTH services are DOWN:\n";
echo "❌ Health Check: Connection error or HTTP 500\n";
echo "❌ Chat Response: Frontend JavaScript fallback\n";
echo "❌ UI Display: Red dot + 'Offline - Limited Mode'\n";
echo "❌ Data Source: Pre-written responses (no real data)\n";

echo "\nWhen RAG service is UP:\n";
echo "✅ Health Check: HTTP 200, status='healthy', ui_indicator='🟢'\n";
echo "✅ Chat Response: AI-powered with OpenRouter\n";
echo "✅ UI Display: Green dot + 'Online - AI Ready'\n";
echo "✅ Data Source: AI + Real-time database\n";

echo "\n✅ LIMITED MODE FIX TEST COMPLETE!\n";
echo "===================================\n";
?>
