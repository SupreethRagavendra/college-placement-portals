<?php

require_once 'vendor/autoload.php';

use App\Services\SupabaseService;
use Illuminate\Support\Facades\Config;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 Testing Email Notification Direct Call\n";
echo "==========================================\n\n";

try {
    $supabaseService = new SupabaseService();
    
    $email = 'supreethvennila@gmail.com';
    $name = 'Supreeth Vennila';
    $status = 'approved';
    
    echo "📧 Test Parameters:\n";
    echo "Email: {$email}\n";
    echo "Name: {$name}\n";
    echo "Status: {$status}\n\n";
    
    echo "📤 Sending test email...\n";
    
    // Try to send email
    $result = $supabaseService->sendStatusEmail($email, $name, $status);
    
    echo "✅ Email sent successfully!\n";
    echo "Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    
    // Let's also check configuration
    echo "\n🔧 Configuration Check:\n";
    echo "Supabase URL: " . config('supabase.url', 'Not set') . "\n";
    echo "Supabase Anon Key: " . (config('supabase.anon_key') ? 'Set' : 'Not set') . "\n";
}

echo "\n📋 Next Steps if email function doesn't exist:\n";
echo "1. The Supabase Edge Function needs to be deployed\n";
echo "2. You can manually deploy it using Supabase CLI\n";
echo "3. Or use the Supabase dashboard to create the function\n";