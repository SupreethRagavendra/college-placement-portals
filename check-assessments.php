<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$assessments = DB::table('assessments')->select('id', 'name', 'category')->get();

echo "Current assessments:\n";
foreach ($assessments as $assessment) {
    echo "ID: {$assessment->id}, Name: {$assessment->name}, Category: {$assessment->category}\n";
}
