<?php

// Fix duplicate assessments - Keep only the 2 correct ones

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
echo "FIXING DUPLICATE ASSESSMENTS\n";
echo "==========================================\n\n";

// Get all assessments
$allAssessments = Capsule::table('assessments')
    ->select('id', 'name', 'description', 'is_active', 'status')
    ->get();

echo "Current assessments in database:\n\n";
foreach ($allAssessments as $assessment) {
    $statusLabel = $assessment->is_active ? '✅ Active' : '❌ Inactive';
    echo "{$statusLabel} | ID: {$assessment->id} | {$assessment->name}\n";
}

echo "\n------------------------------------------\n";
echo "ACTIONS:\n";
echo "Keeping only these 2 assessments:\n";
echo "1. Aptitude Assessment - Logical Reasoning\n";
echo "2. Technical Assessment - Programming Fundamentals\n";
echo "\nDeactivating duplicate 'Test Assessment' entries...\n";
echo "------------------------------------------\n\n";

// Deactivate all "Test Assessment" entries
$updated = Capsule::table('assessments')
    ->where('name', 'Test Assessment')
    ->update([
        'is_active' => false,
        'status' => 'draft',
        'updated_at' => date('Y-m-d H:i:s')
    ]);

echo "✅ Deactivated {$updated} duplicate assessment(s)\n\n";

// Verify the fix
echo "==========================================\n";
echo "VERIFICATION - Active Assessments Now:\n";
echo "==========================================\n\n";

$activeAssessments = Capsule::table('assessments')
    ->where('is_active', true)
    ->select('id', 'name', 'description', 'category')
    ->get();

if ($activeAssessments->count() === 2) {
    echo "✅ Perfect! Now showing exactly 2 assessments:\n\n";
    foreach ($activeAssessments as $assessment) {
        echo "📝 {$assessment->name}\n";
        echo "   Category: {$assessment->category}\n";
        echo "   Description: {$assessment->description}\n";
        echo "   ---\n";
    }
    echo "\n✅ Fix complete! Your chatbot will now show only these 2 assessments.\n";
} else {
    echo "⚠️ Warning: Found {$activeAssessments->count()} active assessments. Expected 2.\n";
    foreach ($activeAssessments as $assessment) {
        echo "- {$assessment->name} (ID: {$assessment->id})\n";
    }
}

echo "\n==========================================\n";
echo "NEXT STEP:\n";
echo "==========================================\n";
echo "Run this command to sync the RAG service:\n";
echo "curl -X POST http://localhost:8001/sync-database\n";
echo "\nOr restart the RAG service to reload the knowledge base.\n";
echo "==========================================\n\n";

?>

