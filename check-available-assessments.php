<?php

// Check available assessments (what the chatbot should show)

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Database connection
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'pgsql',
    'host'      => 'db.wkqbukidxmzbgwauncrl.supabase.co',
    'database'  => 'postgres',
    'username'  => 'postgres',
    'password'  => 'Supreeeth24#',
    'port'      => 5432,
    'charset'   => 'utf8',
    'prefix'    => '',
    'schema'    => 'public',
    'sslmode'   => 'require',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "\n==========================================\n";
echo "AVAILABLE ASSESSMENTS (What Chatbot Should Show)\n";
echo "==========================================\n\n";

// Get available assessments (same logic as ChatbotController)
$availableAssessments = Capsule::table('assessments')
    ->where('is_active', true)
    ->where(function($query) {
        $query->whereNull('start_date')
            ->orWhere('start_date', '<=', date('Y-m-d H:i:s'));
    })
    ->where(function($query) {
        $query->whereNull('end_date')
            ->orWhere('end_date', '>=', date('Y-m-d H:i:s'));
    })
    ->select('id', 'name', 'description', 'total_time', 'duration', 'category')
    ->get();

if ($availableAssessments->isEmpty()) {
    echo "❌ No assessments are currently available.\n";
    echo "\nThis is what the chatbot will say:\n";
    echo "\"There are currently no available assessments. Please check back later or contact your administrator.\"\n";
} else {
    echo "✅ Found " . count($availableAssessments) . " available assessment(s):\n\n";
    
    foreach ($availableAssessments as $assessment) {
        echo "📝 " . $assessment->name . "\n";
        echo "   Description: " . ($assessment->description ?? 'N/A') . "\n";
        echo "   Duration: " . ($assessment->total_time ?? $assessment->duration ?? 30) . " minutes\n";
        echo "   Category: " . ($assessment->category ?? 'General') . "\n";
        
        // Count questions
        $questionCount = Capsule::table('assessment_questions')
            ->where('assessment_id', $assessment->id)
            ->count();
        echo "   Questions: " . $questionCount . "\n";
        echo "   ---\n";
    }
    
    echo "\nThis is what the chatbot will say:\n";
    echo "\"Here are the available assessments:\n\n";
    foreach ($availableAssessments as $assessment) {
        echo "• " . $assessment->name . " (" . ($assessment->total_time ?? $assessment->duration ?? 30) . " minutes)\n";
    }
    echo "\nClick 'View Assessments' to see all details and start a test.\"\n";
}

echo "\n==========================================\n";
echo "ALL ASSESSMENTS IN DATABASE\n";
echo "==========================================\n\n";

$allAssessments = Capsule::table('assessments')
    ->select('id', 'name', 'is_active', 'status', 'start_date', 'end_date')
    ->get();

foreach ($allAssessments as $assessment) {
    $status = $assessment->is_active ? '✅ Active' : '❌ Inactive';
    echo $status . " | " . $assessment->name . "\n";
    echo "     Status: " . $assessment->status . "\n";
    echo "     Start: " . ($assessment->start_date ?? 'Not set') . "\n";
    echo "     End: " . ($assessment->end_date ?? 'Not set') . "\n";
    echo "     ---\n";
}

echo "\n✨ To make an assessment available:\n";
echo "1. Set is_active = true\n";
echo "2. Set status = 'published'\n";
echo "3. Check start_date and end_date (or leave null)\n";

?>
