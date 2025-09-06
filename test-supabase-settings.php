<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\SupabaseService;

echo "=== Supabase Settings Test ===\n\n";

// Test current configuration
echo "Current Laravel Configuration:\n";
echo "   APP_URL: " . config('app.url') . "\n";
echo "   SUPABASE_URL: " . config('supabase.url') . "\n";
echo "   REDIRECT_URL: " . config('supabase.redirect_url') . "\n\n";

// Test Supabase API settings
echo "Testing Supabase API Settings:\n";
try {
    $client = new GuzzleHttp\Client(['timeout' => 10]);
    $response = $client->get(config('supabase.url') . '/auth/v1/settings', [
        'headers' => [
            'apikey' => config('supabase.anon_key'),
            'Content-Type' => 'application/json',
        ]
    ]);
    
    $settings = json_decode($response->getBody()->getContents(), true);
    
    echo "   Site URL: " . ($settings['SITE_URL'] ?? 'Not set') . "\n";
    echo "   Redirect URLs: " . json_encode($settings['REDIRECT_URLS'] ?? []) . "\n";
    
    if (isset($settings['SITE_URL']) && $settings['SITE_URL'] !== 'http://localhost:8000') {
        echo "   ⚠️  WARNING: Site URL is not set to http://localhost:8000\n";
    }
    
    if (!isset($settings['REDIRECT_URLS']) || !in_array('http://localhost:8000/auth/callback', $settings['REDIRECT_URLS'])) {
        echo "   ⚠️  WARNING: Redirect URL http://localhost:8000/auth/callback not found\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Error fetching Supabase settings: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\nIf you see warnings above, you need to update your Supabase dashboard settings.\n";
