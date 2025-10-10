<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$table = $argv[1] ?? 'assessments';

$columns = Schema::getColumnListing($table);
echo ucfirst($table) . " table columns:\n";
foreach ($columns as $column) {
    echo "- {$column}\n";
}
