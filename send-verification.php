<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\SupabaseService;
use Illuminate\Support\Str;

$email = $argv[1] ?? null;
$name = $argv[2] ?? 'Test User';
$role = $argv[3] ?? 'student';

if (!$email) {
	fwrite(STDERR, "Usage: php send-verification.php <email> [name] [role]\n");
	exit(1);
}

$service = new SupabaseService();
$randomPassword = Str::random(16) . 'aA1!';

echo "Sending verification for: {$email}\n";
echo "Redirect URL: " . config('supabase.redirect_url') . "\n";

try {
	// Try signUp (this will send the verification email if user is not confirmed)
	$response = $service->signUp($email, $randomPassword, [
		'name' => $name,
		'role' => $role,
	]);
	echo "SignUp triggered. Check your inbox/spam for the verification email.\n";
	echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
	exit(0);
} catch (\Exception $e) {
	$err = $e->getMessage();
	echo "SignUp failed: {$err}\n";
}

// Fallback: generate verification link via admin endpoint if user might already exist
try {
	$client = new \GuzzleHttp\Client([ 'timeout' => 20, 'verify' => false ]);
	$url = config('supabase.url') . '/auth/v1/admin/generate_link';
	$payload = [
		'type' => 'signup',
		'email' => $email,
		'options' => [
			'data' => [ 'name' => $name, 'role' => $role ],
			'redirect_to' => config('supabase.redirect_url'),
		],
	];
	$res = $client->post($url, [
		'headers' => [
			'apikey' => config('supabase.service_role_key'),
			'Authorization' => 'Bearer ' . config('supabase.service_role_key'),
			'Content-Type' => 'application/json',
		],
		'json' => $payload,
	]);
	$body = json_decode($res->getBody()->getContents(), true);
	echo "Generated verification link (admin):\n";
	echo ($body['action_link'] ?? 'no_link_returned') . "\n";
	echo "Open the link to verify the email.\n";
	exit(0);
} catch (\Exception $e) {
	fwrite(STDERR, "Failed to generate admin verification link: " . $e->getMessage() . "\n");
	exit(2);
}
