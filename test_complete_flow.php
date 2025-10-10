<?php
/**
 * Test Complete RAG Modes Flow
 * Verify all three modes work correctly with proper authentication handling
 */

echo "🔍 TESTING COMPLETE RAG MODES FLOW\n";
echo "=================================\n\n";

// Test 1: Health Check (should work without auth)
echo "🔍 TEST 1: Health Check (No Auth Required)\n";
echo "-----------------------------------------\n";

$healthUrl = 'http://localhost:8000/rag-health';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $healthUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Health Check (HTTP $httpCode): ";
if ($httpCode === 200) {
    $data = json_decode($response, true);
    if ($data['status'] === 'limited') {
        echo "✅ Limited Mode (RAG down, Laravel working)\n";
        echo "   UI: " . $data['ui_indicator'] . " " . $data['ui_text'] . "\n";
    } elseif ($data['status'] === 'healthy') {
        echo "✅ RAG Active (Both services working)\n";
        echo "   UI: " . $data['ui_indicator'] . " " . $data['ui_text'] . "\n";
    } else {
        echo "❓ Unknown status: " . $data['status'] . "\n";
    }
} else {
    echo "❌ Failed (HTTP $httpCode)\n";
}

echo "\n";

// Test 2: Chat Endpoint (requires auth)
echo "🔍 TEST 2: Chat Endpoint (Auth Required)\n";
echo "---------------------------------------\n";

$chatUrl = 'http://localhost:8000/student/rag-chat';
$postData = json_encode(['message' => 'What assessments are available?']);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $chatUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-CSRF-TOKEN: test'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Chat Endpoint (HTTP $httpCode): ";
if ($httpCode === 401) {
    echo "✅ Authentication Required (Expected)\n";
    echo "   JavaScript should show: 'Please log in to use the AI assistant'\n";
} elseif ($httpCode === 200) {
    echo "✅ Success (User is authenticated)\n";
    $data = json_decode($response, true);
    if ($data['model_used'] === 'limited') {
        echo "   Mode: Limited Mode (🟡) - Database responses\n";
    } elseif ($data['model_used'] === 'offline') {
        echo "   Mode: Offline Mode (🔴) - Frontend fallback\n";
    } else {
        echo "   Mode: " . $data['model_used'] . "\n";
    }
} else {
    echo "❓ Unexpected response (HTTP $httpCode)\n";
}

echo "\n";

// Test 3: Expected Behavior Summary
echo "🔍 TEST 3: Expected Behavior Summary\n";
echo "-----------------------------------\n";

echo "🟢 RAG ACTIVE MODE:\n";
echo "   - RAG service: Running\n";
echo "   - Laravel: Working\n";
echo "   - User: Authenticated\n";
echo "   - Health: HTTP 200, status='healthy'\n";
echo "   - Chat: HTTP 200, AI responses\n";
echo "   - UI: Green dot + 'Online - AI Ready'\n\n";

echo "🟡 LIMITED MODE:\n";
echo "   - RAG service: Down\n";
echo "   - Laravel: Working\n";
echo "   - User: Authenticated\n";
echo "   - Health: HTTP 200, status='limited'\n";
echo "   - Chat: HTTP 200, database responses\n";
echo "   - UI: Yellow dot + 'Limited Mode'\n\n";

echo "🔴 OFFLINE MODE:\n";
echo "   - RAG service: Down\n";
echo "   - Laravel: Down\n";
echo "   - User: Any\n";
echo "   - Health: Connection error\n";
echo "   - Chat: Connection error\n";
echo "   - UI: Red dot + 'Offline - Limited Mode'\n\n";

echo "🔐 AUTHENTICATION REQUIRED:\n";
echo "   - RAG service: Any\n";
echo "   - Laravel: Working\n";
echo "   - User: Not authenticated\n";
echo "   - Health: HTTP 200\n";
echo "   - Chat: HTTP 401\n";
echo "   - UI: Yellow dot + 'Authentication Required'\n\n";

// Test 4: JavaScript Error Handling
echo "🔍 TEST 4: JavaScript Error Handling\n";
echo "-----------------------------------\n";

$jsFile = 'public/js/chatbot.js';
if (file_exists($jsFile)) {
    $content = file_get_contents($jsFile);
    
    $checks = [
        'HTTP 401 handling' => strpos($content, 'response.status === 401') !== false,
        'Limited mode detection' => strpos($content, 'errorData.model_used === \'limited\'') !== false,
        'Authentication message' => strpos($content, 'Please log in to use the AI assistant') !== false,
        'Limited mode indicator' => strpos($content, 'Limited Mode') !== false,
        'Status indicators' => strpos($content, 'statusIndicator') !== false
    ];
    
    foreach ($checks as $check => $result) {
        echo ($result ? '✅' : '❌') . " $check\n";
    }
} else {
    echo "❌ JavaScript file not found\n";
}

echo "\n";

// Test 5: Current Status
echo "🔍 TEST 5: Current System Status\n";
echo "-------------------------------\n";

// Check if RAG service is running
$ragHealthUrl = 'http://localhost:8001/health';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $ragHealthUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);

$ragResponse = curl_exec($ch);
$ragHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "RAG Service: ";
if ($ragHttpCode === 200) {
    echo "✅ Running (Port 8001)\n";
} else {
    echo "❌ Down (Port 8001)\n";
}

echo "Laravel: ";
if ($httpCode === 200 || $httpCode === 401) {
    echo "✅ Running (Port 8000)\n";
} else {
    echo "❌ Down (Port 8000)\n";
}

echo "\nExpected Mode: ";
if ($ragHttpCode === 200 && ($httpCode === 200 || $httpCode === 401)) {
    echo "🟢 RAG ACTIVE (Both services working)\n";
} elseif ($ragHttpCode !== 200 && ($httpCode === 200 || $httpCode === 401)) {
    echo "🟡 LIMITED MODE (RAG down, Laravel working)\n";
} else {
    echo "🔴 OFFLINE MODE (Both services down)\n";
}

echo "\n✅ COMPLETE FLOW TEST COMPLETE!\n";
echo "===============================\n";
?>
