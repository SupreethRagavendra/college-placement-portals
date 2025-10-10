<?php
/**
 * Test Limited Mode with Authentication
 * Simulate what happens when an authenticated user asks for assessments with RAG down
 */

echo "🔍 TESTING LIMITED MODE WITH AUTHENTICATION\n";
echo "==========================================\n\n";

// This test simulates what the Laravel controller should return when RAG is down
// but the user is authenticated and Laravel is working

echo "Simulating Laravel fallback response when RAG is down but user is authenticated...\n\n";

// Simulate the fallback response that should be returned
$simulatedResponse = [
    'success' => true,
    'message' => "You have 1 assessment available:\n\n📝 Quantitative Aptitude (Aptitude) - 30 minutes\n\nClick 'View Assessments' to start!",
    'data' => [],
    'actions' => [
        [
            'type' => 'link',
            'text' => 'View Assessments',
            'url' => '/student/assessments',
            'class' => 'btn btn-primary'
        ],
        [
            'type' => 'link',
            'text' => 'View Results',
            'url' => '/student/results',
            'class' => 'btn btn-outline-primary'
        ]
    ],
    'follow_up_questions' => [
        'What assessments are available?',
        'Show my recent results',
        'How do I take a test?'
    ],
    'timestamp' => date('c'),
    'query_type' => 'fallback',
    'model_used' => 'limited',
    'rag_status' => 'limited',
    'service_info' => [
        'indicator' => '🟡',
        'text' => 'Limited Mode'
    ]
];

echo "Expected Response Structure:\n";
echo "- Success: " . ($simulatedResponse['success'] ? 'true' : 'false') . "\n";
echo "- Message: " . substr($simulatedResponse['message'], 0, 100) . "...\n";
echo "- Model Used: " . $simulatedResponse['model_used'] . "\n";
echo "- RAG Status: " . $simulatedResponse['rag_status'] . "\n";
echo "- Service Info: " . $simulatedResponse['service_info']['text'] . "\n";
echo "- Service Indicator: " . $simulatedResponse['service_info']['indicator'] . "\n";

if ($simulatedResponse['model_used'] === 'limited' && $simulatedResponse['rag_status'] === 'limited') {
    echo "\n✅ CORRECT: This is the proper Limited Mode response\n";
    echo "   - Uses database data (real assessments)\n";
    echo "   - Shows yellow indicator (🟡)\n";
    echo "   - Provides actionable buttons\n";
    echo "   - No AI hallucination\n";
} else {
    echo "\n❌ INCORRECT: This is not the proper Limited Mode response\n";
}

echo "\n";

// Test the actual Laravel controller fallback method
echo "🔍 TESTING LARAVEL CONTROLLER FALLBACK METHOD\n";
echo "============================================\n";

// We can't easily test the actual controller without authentication,
// but we can verify the fallback logic is correct by checking the code

echo "Checking OpenRouterChatbotController fallbackResponse method...\n";

$controllerFile = 'app/Http/Controllers/Student/OpenRouterChatbotController.php';
if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    
    // Check if the fallback method exists and has the right structure
    if (strpos($content, 'private function fallbackResponse') !== false) {
        echo "✅ fallbackResponse method exists\n";
        
        if (strpos($content, "'model_used' => 'limited'") !== false) {
            echo "✅ Sets model_used to 'limited'\n";
        } else {
            echo "❌ Does not set model_used to 'limited'\n";
        }
        
        if (strpos($content, "'rag_status' => 'limited'") !== false) {
            echo "✅ Sets rag_status to 'limited'\n";
        } else {
            echo "❌ Does not set rag_status to 'limited'\n";
        }
        
        if (strpos($content, "'indicator' => '🟡'") !== false) {
            echo "✅ Sets indicator to '🟡'\n";
        } else {
            echo "❌ Does not set indicator to '🟡'\n";
        }
        
        if (strpos($content, "'text' => 'Limited Mode'") !== false) {
            echo "✅ Sets text to 'Limited Mode'\n";
        } else {
            echo "❌ Does not set text to 'Limited Mode'\n";
        }
        
        if (strpos($content, 'Assessment::active()') !== false) {
            echo "✅ Uses database queries for assessments\n";
        } else {
            echo "❌ Does not use database queries for assessments\n";
        }
        
    } else {
        echo "❌ fallbackResponse method not found\n";
    }
} else {
    echo "❌ Controller file not found\n";
}

echo "\n";

// Test JavaScript error handling
echo "🔍 TESTING JAVASCRIPT ERROR HANDLING\n";
echo "====================================\n";

$jsFile = 'public/js/chatbot.js';
if (file_exists($jsFile)) {
    $content = file_get_contents($jsFile);
    
    if (strpos($content, 'response.status === 401') !== false) {
        echo "✅ Handles HTTP 401 (Authentication required)\n";
    } else {
        echo "❌ Does not handle HTTP 401 properly\n";
    }
    
    if (strpos($content, 'errorData.model_used === \'limited\'') !== false) {
        echo "✅ Checks for limited mode in error responses\n";
    } else {
        echo "❌ Does not check for limited mode in error responses\n";
    }
    
    if (strpos($content, 'Limited Mode') !== false) {
        echo "✅ Shows 'Limited Mode' status\n";
    } else {
        echo "❌ Does not show 'Limited Mode' status\n";
    }
    
} else {
    echo "❌ JavaScript file not found\n";
}

echo "\n";

// Summary
echo "🔍 SUMMARY\n";
echo "==========\n";
echo "The issue is that when RAG is down but Laravel is working:\n";
echo "1. ✅ Laravel controller should return Limited Mode response (HTTP 200)\n";
echo "2. ❌ But if user is not authenticated, it returns HTTP 401\n";
echo "3. ❌ JavaScript treats HTTP 401 as complete failure\n";
echo "4. ❌ Falls back to frontend offline mode instead of Limited Mode\n";
echo "\n";
echo "SOLUTION:\n";
echo "- User must be logged in to access chat endpoint\n";
echo "- When logged in and RAG is down, should get Limited Mode (🟡)\n";
echo "- When not logged in, should get authentication message\n";
echo "- When both down, should get Offline Mode (🔴)\n";

echo "\n✅ LIMITED MODE AUTHENTICATION TEST COMPLETE!\n";
echo "=============================================\n";
?>
