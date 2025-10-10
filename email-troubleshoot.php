<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Email Delivery Troubleshooting Tool\n";
echo "=====================================\n\n";

$email = 'supreethvennila@gmail.com';
$name = 'Supreeth Vennila';

echo "Target Email: {$email}\n";
echo "Formspree Endpoint: https://formspree.io/f/xanpndqw\n\n";

// Test 1: Direct Formspree API Test
echo "📧 Test 1: Direct Formspree API Test\n";
echo "-------------------------------------\n";

try {
    $formspreeData = [
        'email' => $email,
        'name' => $name,
        'subject' => '🧪 Direct Test - College Placement Portal',
        'message' => "This is a direct test email to verify Formspree delivery.\n\nTest timestamp: " . date('Y-m-d H:i:s') . "\n\nIf you receive this email, the Formspree integration is working correctly.",
        '_replyto' => $email,
        '_subject' => '🧪 Direct Test - College Placement Portal'
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://formspree.io/f/xanpndqw',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($formspreeData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_VERBOSE => true
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "HTTP Status Code: {$httpCode}\n";
    echo "Response: " . ($response ?: 'No response') . "\n";
    echo "cURL Error: " . ($error ?: 'None') . "\n\n";

    if ($httpCode === 200) {
        echo "✅ Direct Formspree test sent successfully!\n";
    } else {
        echo "❌ Direct Formspree test failed\n";
    }

} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Check Formspree Configuration
echo "🔧 Test 2: Formspree Configuration Check\n";
echo "------------------------------------------\n";

try {
    // Try to get information about the Formspree form
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://formspree.io/f/xanpndqw',
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Formspree Form Status Check: {$httpCode}\n";
    echo "Response: " . ($response ?: 'No response') . "\n\n";

} catch (Exception $e) {
    echo "❌ Configuration check error: " . $e->getMessage() . "\n\n";
}

// Test 3: Alternative email method using PHP mail()
echo "📮 Test 3: PHP Native Mail Test\n";
echo "--------------------------------\n";

$to = $email;
$subject = '🧪 PHP Mail Test - College Placement Portal';
$message = "This is a test email using PHP's native mail() function.\n\n";
$message .= "Test timestamp: " . date('Y-m-d H:i:s') . "\n";
$message .= "If you receive this, PHP mail is working on your system.\n";

$headers = [
    'From: noreply@collegeportal.local',
    'Reply-To: supreethvennila@gmail.com',
    'Content-Type: text/plain; charset=UTF-8'
];

if (function_exists('mail')) {
    $mailResult = mail($to, $subject, $message, implode("\r\n", $headers));
    echo "PHP mail() function: " . ($mailResult ? "✅ Available" : "❌ Failed") . "\n";
} else {
    echo "PHP mail() function: ❌ Not available\n";
}

echo "\n";

// Test 4: SMTP Configuration Check
echo "⚙️ Test 4: Laravel Mail Configuration\n";
echo "------------------------------------\n";

try {
    $mailDriver = config('mail.default');
    $mailHost = config('mail.mailers.smtp.host');
    $mailPort = config('mail.mailers.smtp.port');
    $mailUsername = config('mail.mailers.smtp.username');
    $mailFrom = config('mail.from.address');

    echo "Mail Driver: " . ($mailDriver ?: 'Not set') . "\n";
    echo "SMTP Host: " . ($mailHost ?: 'Not set') . "\n";
    echo "SMTP Port: " . ($mailPort ?: 'Not set') . "\n";
    echo "SMTP Username: " . ($mailUsername ? 'Set' : 'Not set') . "\n";
    echo "From Address: " . ($mailFrom ?: 'Not set') . "\n";

} catch (Exception $e) {
    echo "❌ Configuration error: " . $e->getMessage() . "\n";
}

echo "\n";

// Troubleshooting recommendations
echo "🛠️ Troubleshooting Recommendations\n";
echo "==================================\n";
echo "1. Check your spam/junk folder\n";
echo "2. Verify Formspree form settings at https://formspree.io/forms\n";
echo "3. Check if your email provider blocks automated emails\n";
echo "4. Try using a different test email address\n";
echo "5. Check Formspree dashboard for delivery logs\n";
echo "6. Consider implementing direct SMTP for more reliable delivery\n\n";

echo "🔗 Formspree Dashboard: https://formspree.io/forms\n";
echo "📧 Test Email: {$email}\n";
echo "⏰ Test completed at: " . date('Y-m-d H:i:s') . "\n";