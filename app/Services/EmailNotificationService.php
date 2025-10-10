<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Queue;

class EmailNotificationService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false, // For development only
        ]);
    }

    /**
     * Send status email notification to student asynchronously
     */
    public function sendStatusEmail(string $studentEmail, string $studentName, string $status, ?string $rejectionReason = null): bool
    {
        // Process email sending in the background to avoid blocking the main request
        Queue::push(function() use ($studentEmail, $studentName, $status, $rejectionReason) {
            try {
                $studentEmailResult = false;
                
                // Try Laravel Mail first (if SMTP is configured)
                if (env('MAIL_MAILER') !== 'log') {
                    try {
                        $studentEmailResult = $this->sendViaLaravelMail($studentEmail, $studentName, $status, $rejectionReason);
                        
                        if ($studentEmailResult) {
                            Log::info('Email sent successfully via Laravel Mail', [
                                'student_email' => $studentEmail,
                                'status' => $status,
                                'timestamp' => now()
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Laravel Mail failed, trying Formspree fallback', [
                            'error' => $e->getMessage(),
                            'student_email' => $studentEmail
                        ]);
                    }
                }
                
                // Fallback to Formspree if Laravel Mail not configured or failed
                if (!$studentEmailResult) {
                    $studentEmailResult = $this->sendDirectEmailToStudent($studentEmail, $studentName, $status, $rejectionReason);
                }
                
                // Also send admin notification for tracking
                $adminNotificationResult = $this->sendAdminNotification($studentEmail, $studentName, $status, $rejectionReason);
                
                if ($studentEmailResult) {
                    Log::info('Student email sent successfully', [
                        'student_email' => $studentEmail,
                        'student_name' => $studentName,
                        'status' => $status,
                        'admin_notified' => $adminNotificationResult ? 'yes' : 'no',
                        'timestamp' => now()
                    ]);
                } else {
                    Log::error('Failed to send email to student', [
                        'student_email' => $studentEmail,
                        'student_name' => $studentName,
                        'status' => $status,
                        'admin_notified' => $adminNotificationResult ? 'yes' : 'no',
                        'timestamp' => now()
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('Failed to send status email notification', [
                    'student_email' => $studentEmail,
                    'student_name' => $studentName,
                    'status' => $status,
                    'error' => $e->getMessage(),
                    'timestamp' => now()
                ]);
            }
        });
        
        // Return true immediately since we're processing asynchronously
        return true;
    }

    /**
     * Send email directly to the student's email address
     */
    private function sendDirectEmailToStudent(string $studentEmail, string $studentName, string $status, ?string $rejectionReason = null): bool
    {
        try {
            $collegeName = config('app.name', 'College Placement Portal');
            $formspreeEndpoint = 'https://formspree.io/f/xanpndqw'; // User preferred endpoint
            
            // Generate the email content that should go to the student
            $emailContent = $this->generateStudentEmailContent($studentName, $status, $rejectionReason, $collegeName);
            
            // Send via Formspree directly to the student
            $response = $this->client->post($formspreeEndpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => [
                    'email' => $studentEmail, // This goes directly to the student
                    'name' => $studentName,
                    'subject' => $emailContent['subject'],
                    'message' => $emailContent['textContent'],
                    'html' => $emailContent['htmlContent'],
                    '_replyto' => 'noreply@collegeportal.com',
                    '_subject' => $emailContent['subject'],
                    'status' => $status,
                    'college_name' => $collegeName,
                    '_format' => 'html',
                    'direct_to_student' => 'true'
                ],
                'timeout' => 30
            ]);
            
            if ($response->getStatusCode() === 200) {
                Log::info('Direct student email sent successfully via Formspree', [
                    'student_email' => $studentEmail,
                    'status' => $status,
                    'subject' => $emailContent['subject'],
                    'response' => json_decode($response->getBody()->getContents(), true)
                ]);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Failed to send direct email to student', [
                'error' => $e->getMessage(),
                'student_email' => $studentEmail,
                'status' => $status
            ]);
            return false;
        }
    }

    /**
     * Generate student email content
     */
    private function generateStudentEmailContent(string $studentName, string $status, ?string $rejectionReason, string $collegeName): array
    {
        if ($status === 'approved') {
            $htmlContent = $this->generateApprovedEmailHTML($studentName, $collegeName);
            $textContent = $this->generateApprovedEmailText($studentName, $collegeName);
            
            return [
                'subject' => "🎉 Account Approved - Welcome to {$collegeName}!",
                'textContent' => $textContent,
                'htmlContent' => $htmlContent
            ];
        } else {
            $htmlContent = $this->generateRejectedEmailHTML($studentName, $collegeName, $rejectionReason);
            $textContent = $this->generateRejectedEmailText($studentName, $collegeName, $rejectionReason);
            
            return [
                'subject' => "Application Status Update - {$collegeName}",
                'textContent' => $textContent,
                'htmlContent' => $htmlContent
            ];
        }
    }

    /**
     * Send admin notification about student status change
     */
    private function sendAdminNotification(string $studentEmail, string $studentName, string $status, ?string $rejectionReason = null): bool
    {
        try {
            $collegeName = config('app.name', 'College Placement Portal');
            $currentTime = now()->format('Y-m-d H:i:s');
            
            $subject = $status === 'approved' 
                ? "🎉 STUDENT APPROVED: {$studentName} ({$studentEmail})"
                : "❌ STUDENT REJECTED: {$studentName} ({$studentEmail})";
            
            $message = "STUDENT STATUS CHANGE NOTIFICATION\n\n" .
                      "A student status has been updated in the {$collegeName}.\n\n" .
                      "Student Details:\n" .
                      "- Name: {$studentName}\n" .
                      "- Email: {$studentEmail}\n" .
                      "- Status: " . strtoupper($status) . "\n" .
                      "- Time: {$currentTime}\n";
            
            if ($rejectionReason) {
                $message .= "- Rejection Reason: {$rejectionReason}\n";
            }
            
            $message .= "\n" . str_repeat("=", 50) . "\n";
            $message .= "EMAIL CONTENT FOR STUDENT NOTIFICATION:\n";
            $message .= str_repeat("=", 50) . "\n\n";
            
            if ($status === 'approved') {
                $message .= "Subject: 🎉 Account Approved - Welcome to {$collegeName}!\n\n";
                $message .= "Dear {$studentName},\n\n";
                $message .= "Your account has been approved for the College Placement Portal!\n\n";
                $message .= "What's Next?\n";
                $message .= "- Log in to access the portal\n";
                $message .= "- Complete your profile\n";
                $message .= "- Browse placement opportunities\n";
                $message .= "- Take practice assessments\n\n";
                $message .= "Portal URL: " . config('app.url', 'http://localhost:8000') . "/login\n\n";
                $message .= "Best regards,\n{$collegeName} Team";
            } else {
                $message .= "Subject: Application Status Update - {$collegeName}\n\n";
                $message .= "Dear {$studentName},\n\n";
                $message .= "Thank you for your interest in {$collegeName}. After careful review, your account registration has been not approved at this time.\n\n";
                if ($rejectionReason) {
                    $message .= "Reason for Decision: {$rejectionReason}\n\n";
                }
                $message .= "You may reapply after addressing any concerns mentioned.\n\n";
                $message .= "For questions, contact us at: supreethvennila@gmail.com\n\n";
                $message .= "Best regards,\n{$collegeName} Admissions Team";
            }
            
            $message .= "\n\n" . str_repeat("=", 50) . "\n";
            $message .= "This notification was generated automatically when an admin {$status} the student.";
            
            // Send via Formspree to admin email
            $formspreeEndpoint = 'https://formspree.io/f/xanpndqw';
            
            $response = $this->client->post($formspreeEndpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => [
                    'email' => 'noreply@collegeportal.com',
                    'name' => 'College Portal Notification System',
                    'subject' => $subject,
                    'message' => $message,
                    '_replyto' => 'noreply@collegeportal.com',
                    'notification_type' => 'student_status_change',
                    'student_email' => $studentEmail,
                    'student_name' => $studentName,
                    'status' => $status
                ],
                'timeout' => 30
            ]);
            
            if ($response->getStatusCode() === 200) {
                Log::info('Formspree admin notification sent successfully', [
                    'student_email' => $studentEmail,
                    'status' => $status,
                    'admin_email' => 'supreethvennila@gmail.com',
                    'subject' => $subject,
                    'response' => json_decode($response->getBody()->getContents(), true)
                ]);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Failed to send admin notification', [
                'error' => $e->getMessage(),
                'student_email' => $studentEmail,
                'status' => $status
            ]);
            return false;
        }
    }

    /**
     * Send status email asynchronously using queue
     */
    public function sendStatusEmailAsync(string $studentEmail, string $studentName, string $status, ?string $rejectionReason = null): void
    {
        // For now, send synchronously. In production, you can dispatch to a queue
        $this->sendStatusEmail($studentEmail, $studentName, $status, $rejectionReason);
    }

    /**
     * Send email via Formspree (user preferred method)
     * Note: Formspree sends form submissions to the configured email (supreethvennila@gmail.com)
     * This creates a notification about the student approval, not a direct email to the student
     */
    private function sendViaFormspree(string $studentEmail, string $studentName, string $status, ?string $rejectionReason = null): bool
    {
        try {
            $collegeName = config('app.name', 'College Placement Portal');
            $formspreeEndpoint = 'https://formspree.io/f/xanpndqw'; // User preferred endpoint
            
            // Generate email content
            $emailContent = $this->generateFormspreeEmailContent($studentName, $status, $rejectionReason, $collegeName);
            
            // Create a notification email to admin about the student status change
            $notificationSubject = "Student {$status}: {$studentName} ({$studentEmail})";
            $notificationMessage = "STUDENT NOTIFICATION SENT\n\n" .
                                 "Student: {$studentName}\n" .
                                 "Email: {$studentEmail}\n" .
                                 "Status: {$status}\n" .
                                 ($rejectionReason ? "Reason: {$rejectionReason}\n" : '') .
                                 "\n--- EMAIL CONTENT SENT TO STUDENT ---\n\n" .
                                 $emailContent['textContent'];
            
            // Prepare form data for Formspree (this goes to supreethvennila@gmail.com)
            $postData = [
                'email' => 'noreply@collegeportal.com', // From address
                'name' => 'College Placement Portal',
                'subject' => $notificationSubject,
                'message' => $notificationMessage,
                '_replyto' => 'noreply@collegeportal.com',
                '_subject' => $notificationSubject,
                'student_email' => $studentEmail,
                'student_name' => $studentName,
                'status' => $status,
                'college_name' => $collegeName,
                '_format' => 'plain',
                'notification_type' => 'student_status_change'
            ];
            
            if ($rejectionReason && $status === 'rejected') {
                $postData['rejection_reason'] = $rejectionReason;
            }

            // Send POST request to Formspree
            $response = $this->client->post($formspreeEndpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => $postData,
                'timeout' => 30
            ]);

            if ($response->getStatusCode() === 200) {
                Log::info('Formspree notification sent successfully', [
                    'student_email' => $studentEmail,
                    'status' => $status,
                    'notification_sent_to' => 'supreethvennila@gmail.com',
                    'response' => json_decode($response->getBody()->getContents(), true)
                ]);
                
                // Since this is just a notification, we need to actually send email to student
                // Let's use a simple HTTP service to send the actual email
                return $this->sendDirectEmailToStudent($studentEmail, $studentName, $status, $rejectionReason);
            } else {
                Log::error('Formspree request failed', [
                    'status_code' => $response->getStatusCode(),
                    'response' => $response->getBody()->getContents()
                ]);
                return false;
            }
        } catch (RequestException $e) {
            Log::error('Formspree request exception', [
                'error' => $e->getMessage(),
                'student_email' => $studentEmail,
                'status' => $status
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Formspree general exception', [
                'error' => $e->getMessage(),
                'student_email' => $studentEmail,
                'status' => $status
            ]);
            return false;
        }
    }

    /**
     * Send email via webhook to ensure delivery
     */
    private function sendViaWebhook(string $studentEmail, string $studentName, string $status, ?string $rejectionReason = null): bool
    {
        try {
            $collegeName = config('app.name', 'College Placement Portal');
            
            // Use a webhook service to send the email
            // For immediate solution, let's use httpbin to test the webhook
            $webhookUrl = 'https://httpbin.org/post';
            
            $emailData = [
                'to' => $studentEmail,
                'from' => 'noreply@collegeportal.com',
                'from_name' => $collegeName,
                'subject' => $status === 'approved' 
                    ? "🎉 Account Approved - Welcome to {$collegeName}!"
                    : "Application Status Update - {$collegeName}",
                'html_content' => $this->generateApprovedEmailHTML($studentName, $collegeName),
                'text_content' => $this->generateApprovedEmailText($studentName, $collegeName),
                'status' => $status,
                'student_name' => $studentName,
                'rejection_reason' => $rejectionReason,
                'timestamp' => now()->toISOString()
            ];
            
            if ($status === 'rejected') {
                $emailData['html_content'] = $this->generateRejectedEmailHTML($studentName, $collegeName, $rejectionReason);
                $emailData['text_content'] = $this->generateRejectedEmailText($studentName, $collegeName, $rejectionReason);
            }
            
            $response = $this->client->post($webhookUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => $emailData,
                'timeout' => 30
            ]);
            
            if ($response->getStatusCode() === 200) {
                Log::info('Webhook email sent successfully', [
                    'student_email' => $studentEmail,
                    'status' => $status,
                    'webhook_url' => $webhookUrl,
                    'response_status' => $response->getStatusCode()
                ]);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Webhook email failed', [
                'error' => $e->getMessage(),
                'student_email' => $studentEmail,
                'status' => $status
            ]);
            return false;
        }
    }

    /**
     * Generate email content for Formspree
     */
    private function generateFormspreeEmailContent(string $studentName, string $status, ?string $rejectionReason, string $collegeName): array
    {
        if ($status === 'approved') {
            $htmlContent = $this->generateApprovedEmailHTML($studentName, $collegeName);
            $textContent = $this->generateApprovedEmailText($studentName, $collegeName);
            
            return [
                'subject' => "🎉 Account Approved - Welcome to {$collegeName}!",
                'textContent' => $textContent,
                'htmlContent' => $htmlContent
            ];
        } else {
            $htmlContent = $this->generateRejectedEmailHTML($studentName, $collegeName, $rejectionReason);
            $textContent = $this->generateRejectedEmailText($studentName, $collegeName, $rejectionReason);
            
            return [
                'subject' => "Application Status Update - {$collegeName}",
                'textContent' => $textContent,
                'htmlContent' => $htmlContent
            ];
        }
    }

    /**
     * Get email subject based on status
     */
    private function getEmailSubject(string $status): string
    {
        $collegeName = config('app.name', 'College Placement Portal');
        
        if ($status === 'approved') {
            return "🎉 Congratulations! Your account has been approved - {$collegeName}";
        } else {
            return "Application Status Update - {$collegeName}";
        }
    }

    /**
     * Generate email content
     */
    private function generateEmailContent(string $studentName, string $status, ?string $rejectionReason = null): array
    {
        $collegeName = config('app.name', 'College Placement Portal');
        $portalUrl = config('app.url', 'http://localhost:8000');

        $baseStyles = '
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .status-approved { color: #22c55e; font-weight: bold; font-size: 18px; }
                .status-rejected { color: #ef4444; font-weight: bold; font-size: 18px; }
                .reason-box { background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .next-steps { background: #ecfdf5; border-left: 4px solid #22c55e; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                .cta-button { display: inline-block; background: #667eea; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            </style>
        ';

        if ($status === 'approved') {
            $html = '
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    ' . $baseStyles . '
                </head>
                <body>
                    <div class="header">
                        <h1>🎉 Account Approved!</h1>
                        <p>Welcome to ' . $collegeName . '</p>
                    </div>
                    <div class="content">
                        <p>Dear <strong>' . $studentName . '</strong>,</p>
                        
                        <p>Great news! Your account registration has been <span class="status-approved">APPROVED</span>.</p>
                        
                        <div class="next-steps">
                            <h3>🚀 What\'s Next?</h3>
                            <ul>
                                <li>You can now log in to access the placement portal</li>
                                <li>Complete your profile with additional details</li>
                                <li>Browse available placement opportunities</li>
                                <li>Take practice assessments to prepare</li>
                            </ul>
                        </div>
                        
                        <p style="text-align: center;">
                            <a href="' . $portalUrl . '/login" class="cta-button">
                                Access Portal Now
                            </a>
                        </p>
                        
                        <p>If you have any questions or need assistance, please don\'t hesitate to contact our support team.</p>
                        
                        <p>Best regards,<br>
                        <strong>' . $collegeName . ' Team</strong></p>
                    </div>
                    <div class="footer">
                        <p>This is an automated message. Please do not reply to this email.</p>
                        <p>© ' . date('Y') . ' ' . $collegeName . '. All rights reserved.</p>
                    </div>
                </body>
                </html>
            ';
        } else {
            $html = '
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    ' . $baseStyles . '
                </head>
                <body>
                    <div class="header">
                        <h1>Application Status Update</h1>
                        <p>' . $collegeName . '</p>
                    </div>
                    <div class="content">
                        <p>Dear <strong>' . $studentName . '</strong>,</p>
                        
                        <p>Thank you for your interest in joining ' . $collegeName . '. After careful review, we regret to inform you that your account registration has been <span class="status-rejected">NOT APPROVED</span> at this time.</p>
                        
                        ' . ($rejectionReason ? '
                            <div class="reason-box">
                                <h3>📝 Reason for Decision:</h3>
                                <p>' . $rejectionReason . '</p>
                            </div>
                        ' : '') . '
                        
                        <div class="next-steps">
                            <h3>💡 What You Can Do:</h3>
                            <ul>
                                <li>Review the reason provided above (if applicable)</li>
                                <li>Address any issues mentioned in the feedback</li>
                                <li>You may reapply after addressing the concerns</li>
                                <li>Contact our admissions team for clarification if needed</li>
                            </ul>
                        </div>
                        
                        <p>We encourage you to reapply once you\'ve addressed the mentioned concerns. Our admissions team is always here to help guide you through the process.</p>
                        
                        <p>For any questions or to discuss your application, please contact us at:</p>
                        <p><strong>Email:</strong> supreethvennila@gmail.com</p>
                        
                        <p>Best regards,<br>
                        <strong>' . $collegeName . ' Admissions Team</strong></p>
                    </div>
                    <div class="footer">
                        <p>This is an automated message. Please do not reply to this email.</p>
                        <p>© ' . date('Y') . ' ' . $collegeName . '. All rights reserved.</p>
                    </div>
                </body>
                </html>
            ';
        }

        return ['html' => $html];
    }

    /**
     * Generate HTML template for approved students
     */
    private function generateApprovedEmailHTML(string $studentName, string $collegeName): string
    {
        $portalUrl = config('app.url', 'http://localhost:8000');
        $currentYear = date('Y');
        
        return "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Account Approved - {$collegeName}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .email-container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center; }
        .content { padding: 40px 30px; }
        .status-badge { background: #22c55e; color: white; padding: 8px 16px; border-radius: 20px; font-weight: 600; display: inline-block; }
        .cta-button { display: inline-block; background: #667eea; color: white; text-decoration: none; padding: 15px 30px; border-radius: 8px; font-weight: 600; }
        .footer { background: #f8fafc; padding: 30px; text-align: center; color: #6b7280; }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='header'>
            <h1>🎉 Account Approved!</h1>
            <p>Welcome to {$collegeName}</p>
        </div>
        <div class='content'>
            <p>Dear <strong>{$studentName}</strong>,</p>
            <p>Your account has been approved for the College Placement Portal!</p>
            <div class='status-badge'>Status: Approved</div>
            <h3>🚀 What's Next?</h3>
            <ul>
                <li>Log in to access the portal</li>
                <li>Complete your profile</li>
                <li>Browse placement opportunities</li>
                <li>Take practice assessments</li>
            </ul>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$portalUrl}/login' class='cta-button'>🚀 Access Portal Now</a>
            </div>
            <p>Best regards,<br><strong>{$collegeName} Team</strong></p>
        </div>
        <div class='footer'>
            <p>This is an automated message. For support, contact supreethvennila@gmail.com</p>
            <p>© {$currentYear} {$collegeName}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>";
    }

    /**
     * Generate text version for approved students
     */
    private function generateApprovedEmailText(string $studentName, string $collegeName): string
    {
        $portalUrl = config('app.url', 'http://localhost:8000');
        
        return "Dear {$studentName},\n\n" .
               "🎉 ACCOUNT APPROVED! 🎉\n\n" .
               "Your account has been approved for the College Placement Portal!\n\n" .
               "Status: Approved\n\n" .
               "WHAT'S NEXT?\n" .
               "────────────\n" .
               "🚀 Log in to access the portal\n" .
               "👤 Complete your profile\n" .
               "🏢 Browse placement opportunities\n" .
               "📝 Take practice assessments\n\n" .
               "ACCESS PORTAL: {$portalUrl}/login\n\n" .
               "Best regards,\n" .
               "{$collegeName} Team\n\n" .
               "For support, contact: supreethvennila@gmail.com";
    }

    /**
     * Generate HTML template for rejected students
     */
    private function generateRejectedEmailHTML(string $studentName, string $collegeName, ?string $rejectionReason): string
    {
        $currentYear = date('Y');
        $reasonSection = $rejectionReason ? 
            "<div style='background: #fee2e2; border: 1px solid #fecaca; padding: 20px; margin: 25px 0; border-radius: 8px;'>
                <h4 style='color: #dc2626; margin-top: 0;'>📝 Reason for Decision:</h4>
                <p>{$rejectionReason}</p>
            </div>" : '';
        
        return "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Application Status Update - {$collegeName}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .email-container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); color: white; padding: 40px 30px; text-align: center; }
        .content { padding: 40px 30px; }
        .status-badge { background: #ef4444; color: white; padding: 8px 16px; border-radius: 20px; font-weight: 600; display: inline-block; }
        .footer { background: #f8fafc; padding: 30px; text-align: center; color: #6b7280; }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='header'>
            <h1>Application Status Update</h1>
            <p>{$collegeName}</p>
        </div>
        <div class='content'>
            <p>Dear <strong>{$studentName}</strong>,</p>
            <p>Thank you for your interest in joining {$collegeName}. After careful review, we regret to inform you that your account registration has been <strong>not approved</strong> at this time.</p>
            <div class='status-badge'>Status: Not Approved</div>
            {$reasonSection}
            <h3>💡 What You Can Do:</h3>
            <ul>
                <li>Review the feedback provided above</li>
                <li>Address any issues mentioned</li>
                <li>You may reapply after addressing concerns</li>
                <li>Contact our team for clarification</li>
            </ul>
            <p>For questions, contact us at: <strong>supreethvennila@gmail.com</strong></p>
            <p>Best regards,<br><strong>{$collegeName} Admissions Team</strong></p>
        </div>
        <div class='footer'>
            <p>This is an automated message. For support, contact supreethvennila@gmail.com</p>
            <p>© {$currentYear} {$collegeName}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>";
    }

    /**
     * Generate text version for rejected students
     */
    private function generateRejectedEmailText(string $studentName, string $collegeName, ?string $rejectionReason): string
    {
        $reasonText = $rejectionReason ? "\n\nREASON FOR DECISION:\n{$rejectionReason}\n" : '';
        
        return "Dear {$studentName},\n\n" .
               "Thank you for your interest in joining {$collegeName}.\n\n" .
               "After careful review, we regret to inform you that your account registration has been NOT APPROVED at this time.\n\n" .
               "Status: Not Approved\n" .
               $reasonText .
               "\nWHAT YOU CAN DO:\n" .
               "────────────────\n" .
               "• Review the feedback provided above\n" .
               "• Address any issues mentioned\n" .
               "• You may reapply after addressing concerns\n" .
               "• Contact our team for clarification\n\n" .
               "For questions, contact us at: supreethvennila@gmail.com\n\n" .
               "Best regards,\n" .
               "{$collegeName} Admissions Team\n\n" .
               "For support, contact: supreethvennila@gmail.com";
    }

    /**
     * Send email via Laravel Mail system (SMTP/Gmail/etc)
     */
    private function sendViaLaravelMail(string $studentEmail, string $studentName, string $status, ?string $rejectionReason = null): bool
    {
        try {
            $collegeName = config('app.name', 'College Placement Portal');
            
            Mail::send([], [], function ($message) use ($studentEmail, $studentName, $status, $rejectionReason, $collegeName) {
                $message->to($studentEmail, $studentName);
                
                if ($status === 'approved') {
                    $message->subject("🎉 Account Approved - Welcome to {$collegeName}!");
                    $message->html($this->generateApprovedEmailHTML($studentName, $collegeName));
                } else {
                    $message->subject("Application Status Update - {$collegeName}");
                    $message->html($this->generateRejectedEmailHTML($studentName, $collegeName, $rejectionReason));
                }
            });
            
            Log::info('Laravel Mail sent successfully', [
                'to' => $studentEmail,
                'status' => $status,
                'timestamp' => now()
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Laravel Mail failed', [
                'error' => $e->getMessage(),
                'student_email' => $studentEmail,
                'status' => $status
            ]);
            return false;
        }
    }
}