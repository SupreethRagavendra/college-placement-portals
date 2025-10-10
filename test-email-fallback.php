<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "📧 Email Notification Test - Fallback Method\n";
echo "=============================================\n\n";

// Test data
$email = 'supreethvennila@gmail.com';
$name = 'Supreeth Vennila';
$status = 'approved';

echo "📧 Test Parameters:\n";
echo "Email: {$email}\n";
echo "Name: {$name}\n";
echo "Status: {$status}\n\n";

// Create a simple HTML email template
$htmlContent = generateEmailTemplate($name, $status);

echo "✅ Email Template Generated Successfully!\n\n";

echo "📧 Email Content Preview:\n";
echo "========================\n";
echo "Subject: 🎉 Congratulations! Your account has been approved - College Placement Portal\n\n";

// Show text version of email
$textContent = strip_tags($htmlContent);
$textContent = preg_replace('/\s+/', ' ', $textContent);
$textContent = trim($textContent);

echo "Content Preview:\n";
echo $textContent . "\n\n";

echo "📋 Manual Steps to Complete Email Setup:\n";
echo "=========================================\n";
echo "1. Install Supabase CLI manually:\n";
echo "   - Download from: https://github.com/supabase/cli/releases\n";
echo "   - Or use: winget install Supabase.cli\n\n";
echo "2. Deploy the Edge Function:\n";
echo "   - supabase login\n";
echo "   - supabase link --project-ref wkqbukidxmzbgwauncrl\n";
echo "   - supabase functions deploy send-status-email\n\n";
echo "3. Set environment variables:\n";
echo "   - supabase secrets set SENDGRID_API_KEY=your_key\n";
echo "   - supabase secrets set FROM_EMAIL={$email}\n";
echo "   - supabase secrets set FROM_NAME=\"College Placement Portal\"\n\n";

echo "🔗 Alternative: Use Formspree (Based on your preferences)\n";
echo "=========================================================\n";
echo "Your preferred Formspree endpoint: https://formspree.io/f/xanpndqw\n";
echo "Target email: {$email}\n\n";

// Test Formspree integration
echo "📤 Testing Formspree integration...\n";
testFormspreeIntegration($email, $name, $status);

function generateEmailTemplate($studentName, $status) {
    $collegeName = 'College Placement Portal';
    
    if ($status === 'approved') {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .status-approved { color: #22c55e; font-weight: bold; font-size: 18px; }
                .next-steps { background: #ecfdf5; border-left: 4px solid #22c55e; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .cta-button { display: inline-block; background: #667eea; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🎉 Account Approved!</h1>
                <p>Welcome to {$collegeName}</p>
            </div>
            <div class='content'>
                <p>Dear <strong>{$studentName}</strong>,</p>
                
                <p>Great news! Your account registration has been <span class='status-approved'>APPROVED</span>.</p>
                
                <div class='next-steps'>
                    <h3>🚀 What's Next?</h3>
                    <ul>
                        <li>You can now log in to access the placement portal</li>
                        <li>Complete your profile with additional details</li>
                        <li>Browse available placement opportunities</li>
                        <li>Take practice assessments to prepare</li>
                    </ul>
                </div>
                
                <p style='text-align: center;'>
                    <a href='#' class='cta-button'>Access Portal Now</a>
                </p>
                
                <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                
                <p>Best regards,<br><strong>{$collegeName} Team</strong></p>
            </div>
        </body>
        </html>";
    }
    
    return $html;
}

function testFormspreeIntegration($email, $name, $status) {
    $formspreeUrl = 'https://formspree.io/f/xanpndqw';
    
    $postData = [
        'email' => $email,
        'name' => $name,
        'subject' => '🎉 Account Approved - College Placement Portal',
        'message' => "Dear {$name},\n\nYour account has been approved for the College Placement Portal!\n\nStatus: " . ucfirst($status) . "\n\nYou can now access the portal and explore placement opportunities.\n\nBest regards,\nCollege Placement Portal Team",
        '_replyto' => $email
    ];
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $formspreeUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            echo "✅ Formspree test email sent successfully!\n";
            echo "Check your email: {$email}\n";
        } else {
            echo "⚠️  Formspree response code: {$httpCode}\n";
            echo "Response: {$response}\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Formspree test failed: " . $e->getMessage() . "\n";
    }
}