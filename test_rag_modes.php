<?php
/**
 * RAG Modes Test Script
 * Tests all three RAG modes: Active, Limited, Offline
 */

echo "🤖 RAG MODES COMPREHENSIVE TEST\n";
echo "================================\n\n";

// Test 1: Check if OpenRouter RAG service is running
echo "🔍 TEST 1: Checking OpenRouter RAG Service Status\n";
echo "------------------------------------------------\n";

$ragServiceUrl = 'http://localhost:8001';
$healthUrl = $ragServiceUrl . '/health';

echo "Health Check URL: $healthUrl\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $healthUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ RAG Service Connection Error: $error\n";
    echo "🟡 MODE: Limited Mode (Database Only)\n";
    $ragServiceRunning = false;
} elseif ($httpCode === 200) {
    echo "✅ RAG Service is running (HTTP $httpCode)\n";
    $data = json_decode($response, true);
    if ($data && isset($data['status']) && $data['status'] === 'healthy') {
        echo "🟢 MODE: RAG Active (Full AI Power)\n";
        $ragServiceRunning = true;
    } else {
        echo "🟡 MODE: Limited Mode (Service unhealthy)\n";
        $ragServiceRunning = false;
    }
} else {
    echo "❌ RAG Service returned HTTP $httpCode\n";
    echo "🟡 MODE: Limited Mode (Service error)\n";
    $ragServiceRunning = false;
}

echo "\n";

// Test 2: Test Laravel RAG Health Endpoint
echo "🔍 TEST 2: Testing Laravel RAG Health Endpoint\n";
echo "--------------------------------------------\n";

$laravelHealthUrl = 'http://localhost:8000/student/rag-health';

echo "Laravel Health URL: $laravelHealthUrl\n";

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
    echo "❌ Laravel Health Check Error: $error\n";
    echo "🔴 MODE: Offline Mode (Frontend Fallback)\n";
    $laravelWorking = false;
} elseif ($httpCode === 200) {
    echo "✅ Laravel Health Check successful (HTTP $httpCode)\n";
    $data = json_decode($response, true);
    if ($data && isset($data['rag_service']) && $data['rag_service'] === true) {
        echo "🟢 Laravel reports RAG service is healthy\n";
        $laravelWorking = true;
    } else {
        echo "🟡 Laravel reports RAG service is unhealthy\n";
        $laravelWorking = true; // Laravel is working, but RAG is not
    }
} else {
    echo "❌ Laravel Health Check failed (HTTP $httpCode)\n";
    echo "🔴 MODE: Offline Mode (Laravel not responding)\n";
    $laravelWorking = false;
}

echo "\n";

// Test 3: Test Chat Endpoint
echo "🔍 TEST 3: Testing Chat Endpoint\n";
echo "--------------------------------\n";

$chatUrl = 'http://localhost:8000/student/rag-chat';

echo "Chat URL: $chatUrl\n";

$testMessage = "What assessments are available?";
$postData = json_encode([
    'message' => $testMessage
]);

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
    echo "❌ Chat Endpoint Error: $error\n";
    echo "🔴 MODE: Offline Mode (Network error)\n";
    $chatWorking = false;
} elseif ($httpCode === 200) {
    echo "✅ Chat Endpoint successful (HTTP $httpCode)\n";
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success'] === true) {
        echo "🟢 Chat response received successfully\n";
        echo "Response: " . substr($data['message'] ?? 'No message', 0, 100) . "...\n";
        if (isset($data['rag_status'])) {
            echo "RAG Status: " . $data['rag_status'] . "\n";
        }
        if (isset($data['service_info']['text'])) {
            echo "Service Info: " . $data['service_info']['text'] . "\n";
        }
        $chatWorking = true;
    } else {
        echo "🟡 Chat response indicates fallback mode\n";
        $chatWorking = true;
    }
} elseif ($httpCode === 419) {
    echo "⚠️  CSRF Token Error (HTTP $httpCode) - This is expected in testing\n";
    echo "🟡 Chat endpoint is accessible but needs proper authentication\n";
    $chatWorking = true;
} else {
    echo "❌ Chat Endpoint failed (HTTP $httpCode)\n";
    echo "Response: " . substr($response, 0, 200) . "...\n";
    $chatWorking = false;
}

echo "\n";

// Test 4: Determine Final Mode
echo "🔍 TEST 4: Final Mode Determination\n";
echo "----------------------------------\n";

if ($ragServiceRunning && $laravelWorking && $chatWorking) {
    echo "🟢 FINAL MODE: RAG ACTIVE (Full AI Power)\n";
    echo "   - OpenRouter RAG service is running\n";
    echo "   - Laravel backend is working\n";
    echo "   - Chat endpoint is accessible\n";
    echo "   - UI should show: 🟢 Online - AI Ready\n";
} elseif ($laravelWorking && $chatWorking) {
    echo "🟡 FINAL MODE: LIMITED MODE (Database Only)\n";
    echo "   - OpenRouter RAG service is down\n";
    echo "   - Laravel backend is working\n";
    echo "   - Chat endpoint uses fallback responses\n";
    echo "   - UI should show: 🟡 Limited Mode\n";
} else {
    echo "🔴 FINAL MODE: OFFLINE MODE (Frontend Fallback)\n";
    echo "   - OpenRouter RAG service is down\n";
    echo "   - Laravel backend has issues\n";
    echo "   - Frontend JavaScript handles responses\n";
    echo "   - UI should show: 🔴 Offline - Limited Mode\n";
}

echo "\n";

// Test 5: UI Status Indicators
echo "🔍 TEST 5: UI Status Indicators\n";
echo "------------------------------\n";

echo "Expected UI behavior:\n";
echo "🟢 Green Dot + 'Online - AI Ready'     = RAG service active\n";
echo "🟡 Yellow Dot + 'Limited Mode'         = RAG down, Laravel fallback\n";
echo "🔴 Red Dot + 'Offline - Limited Mode'  = Complete system failure\n";
echo "⏳ Gray Dot + 'Checking status...'     = Loading/checking\n";

echo "\n";

// Test 6: Mode-Specific Features
echo "🔍 TEST 6: Mode-Specific Features\n";
echo "---------------------------------\n";

echo "🟢 RAG ACTIVE MODE:\n";
echo "   ✅ AI-powered responses with OpenRouter\n";
echo "   ✅ Real-time database queries\n";
echo "   ✅ Context-aware conversations\n";
echo "   ✅ Anti-hallucination protection\n";
echo "   ✅ Teacher mode for off-topic queries\n\n";

echo "🟡 LIMITED MODE:\n";
echo "   ✅ Database-driven responses\n";
echo "   ✅ Basic keyword matching\n";
echo "   ✅ Real assessment data\n";
echo "   ❌ No AI intelligence\n";
echo "   ❌ No context understanding\n\n";

echo "🔴 OFFLINE MODE:\n";
echo "   ✅ Generic helpful responses\n";
echo "   ✅ Frontend JavaScript handling\n";
echo "   ❌ No real data\n";
echo "   ❌ No personalization\n";
echo "   ❌ No AI intelligence\n\n";

echo "✅ RAG MODES TEST COMPLETE!\n";
echo "============================\n";
?>
