<?php

/**
 * Test Enhanced LIMITED MODE Features
 * Tests all 9 query types with database queries
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\Student\OpenRouterChatbotController;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "=====================================================\n";
echo "TESTING ENHANCED LIMITED MODE - ALL 9 FEATURES\n";
echo "=====================================================\n\n";

// Find a student user
$student = \App\Models\User::where('role', 'student')->where('is_approved', true)->first();

if (!$student) {
    echo "❌ No approved student found.\n";
    exit(1);
}

echo "Testing with Student: {$student->name} (ID: {$student->id})\n\n";

// Authenticate as the student
Auth::login($student);

// Create controller instance
$controller = new OpenRouterChatbotController();

// Test all 9 query types
$testQueries = [
    // 1. Available Assessments
    [
        'category' => '1. AVAILABLE ASSESSMENTS',
        'queries' => [
            "Show available assessments",
            "What tests can I take?",
            "Show me exams"
        ]
    ],
    
    // 2. Results & Performance
    [
        'category' => '2. RESULTS & PERFORMANCE',
        'queries' => [
            "Show my results",
            "What are my scores?",
            "Show my performance"
        ]
    ],
    
    // 3. Statistics & Progress
    [
        'category' => '3. STATISTICS & PROGRESS',
        'queries' => [
            "Show my statistics",
            "How am I doing?",
            "How many tests have I completed?"
        ]
    ],
    
    // 4. Category-Specific
    [
        'category' => '4. CATEGORY-SPECIFIC QUERIES',
        'queries' => [
            "Show technical assessments",
            "Show aptitude tests"
        ]
    ],
    
    // 5. Recent Activity
    [
        'category' => '5. RECENT ACTIVITY',
        'queries' => [
            "What's my recent activity?",
            "Show my last test",
            "What's my latest result?"
        ]
    ],
    
    // 6. Best Performance
    [
        'category' => '6. BEST PERFORMANCE',
        'queries' => [
            "What's my best score?",
            "Show my top performance",
            "What's my highest score?"
        ]
    ],
    
    // 7. Areas for Improvement
    [
        'category' => '7. AREAS FOR IMPROVEMENT',
        'queries' => [
            "What should I improve?",
            "Show my worst score",
            "Where do I need to improve?"
        ]
    ],
    
    // 8. Profile Information
    [
        'category' => '8. PROFILE INFORMATION',
        'queries' => [
            "Show my profile",
            "Who am I?",
            "What's my name?"
        ]
    ],
    
    // 9. Help & Guidance
    [
        'category' => '9. HELP & GUIDANCE',
        'queries' => [
            "Help",
            "What can you do?",
            "How can you help me?"
        ]
    ],
    
    // 10. Default Greeting
    [
        'category' => '10. DEFAULT GREETING',
        'queries' => [
            "Hi",
            "Hello",
            "Hey there"
        ]
    ]
];

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

foreach ($testQueries as $testGroup) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo $testGroup['category'] . "\n";
    echo str_repeat("=", 60) . "\n\n";
    
    foreach ($testGroup['queries'] as $query) {
        $totalTests++;
        echo "Query: \"{$query}\"\n";
        echo str_repeat("-", 60) . "\n";
        
        try {
            // Create mock request
            $request = Request::create('/api/student/chatbot', 'POST', [
                'message' => $query
            ]);
            
            // Use reflection to call the private fallbackResponse method
            $reflection = new ReflectionClass($controller);
            $method = $reflection->getMethod('fallbackResponse');
            $method->setAccessible(true);
            
            // Call the fallback method directly
            $response = $method->invoke($controller, $query, $student->id);
            $responseData = json_decode($response->getContent(), true);
            
            // Check if response is valid
            if ($responseData['success'] && !empty($responseData['message'])) {
                echo "✅ SUCCESS\n";
                $passedTests++;
                
                // Show response preview (first 200 chars)
                $message = $responseData['message'];
                $preview = strlen($message) > 200 ? substr($message, 0, 200) . '...' : $message;
                echo "\nResponse Preview:\n";
                echo "─────────────────\n";
                echo $preview . "\n";
                echo "─────────────────\n";
            } else {
                echo "❌ FAILED: Invalid response\n";
                $failedTests++;
            }
            
        } catch (\Exception $e) {
            echo "❌ ERROR: " . $e->getMessage() . "\n";
            $failedTests++;
        }
        
        echo "\n";
    }
}

// Summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "TEST SUMMARY\n";
echo str_repeat("=", 60) . "\n\n";

echo "Total Tests: {$totalTests}\n";
echo "✅ Passed: {$passedTests}\n";
echo "❌ Failed: {$failedTests}\n";
echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 2) . "%\n\n";

if ($failedTests === 0) {
    echo "🎉 ALL TESTS PASSED! 🎉\n";
    echo "\nThe Enhanced LIMITED MODE is working perfectly!\n";
    echo "\nFeatures Available:\n";
    echo "  1. ✅ Available Assessments\n";
    echo "  2. ✅ Results & Performance\n";
    echo "  3. ✅ Statistics & Progress\n";
    echo "  4. ✅ Category-Specific Queries\n";
    echo "  5. ✅ Recent Activity\n";
    echo "  6. ✅ Best Performance\n";
    echo "  7. ✅ Areas for Improvement\n";
    echo "  8. ✅ Profile Information\n";
    echo "  9. ✅ Help & Guidance\n";
    echo " 10. ✅ Default Greeting\n";
} else {
    echo "⚠️  Some tests failed. Please review the errors above.\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "DETAILED FEATURE TEST\n";
echo str_repeat("=", 60) . "\n\n";

// Test one query from each category in detail
$detailedTests = [
    "Show available assessments" => "Available Assessments",
    "Show my statistics" => "Statistics & Progress",
    "What's my best score?" => "Best Performance",
    "Show my profile" => "Profile Information",
    "Help" => "Help & Guidance"
];

foreach ($detailedTests as $query => $feature) {
    echo "\n{$feature}: \"{$query}\"\n";
    echo str_repeat("-", 60) . "\n";
    
    try {
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('fallbackResponse');
        $method->setAccessible(true);
        
        $response = $method->invoke($controller, $query, $student->id);
        $responseData = json_decode($response->getContent(), true);
        
        echo $responseData['message'] . "\n";
        
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n\n";

echo "Testing complete! 🎉\n";
echo "\nNext Steps:\n";
echo "1. Test in browser with RAG service stopped\n";
echo "2. Try all the query types listed above\n";
echo "3. Verify responses are accurate and helpful\n\n";


