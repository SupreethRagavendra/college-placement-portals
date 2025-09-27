<?php

namespace App\Console\Commands;

use App\Services\SupabaseService;
use Illuminate\Console\Command;

class TestEmailNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test 
                           {email : The recipient email address}
                           {name : The recipient name}
                           {status : The status (approved/rejected)}
                           {--reason= : Rejection reason (only for rejected status)}
                           {--async : Send email asynchronously}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email notification functionality';

    protected $supabaseService;

    /**
     * Create a new command instance.
     */
    public function __construct(SupabaseService $supabaseService)
    {
        parent::__construct();
        $this->supabaseService = $supabaseService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->argument('name');
        $status = $this->argument('status');
        $reason = $this->option('reason');
        $async = $this->option('async');

        // Validate status
        if (!in_array($status, ['approved', 'rejected'])) {
            $this->error('Status must be either "approved" or "rejected"');
            return 1;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email format');
            return 1;
        }

        $this->info('🧪 Testing Email Notification');
        $this->info('==============================');
        $this->newLine();

        $this->table(['Parameter', 'Value'], [
            ['Email', $email],
            ['Name', $name],
            ['Status', $status],
            ['Rejection Reason', $reason ?? 'N/A'],
            ['Async Mode', $async ? 'Yes' : 'No'],
        ]);

        $this->newLine();

        try {
            if ($async) {
                $this->info('📤 Sending email asynchronously...');
                
                $promise = $this->supabaseService->sendStatusEmailAsync(
                    $email,
                    $name,
                    $status,
                    $reason
                );
                
                if ($promise) {
                    $this->info('✅ Email sent asynchronously (check logs for completion)');
                    
                    // Optional: Wait for completion in test mode
                    if ($this->confirm('Wait for email completion? (This may take a few seconds)')) {
                        try {
                            $result = $promise->wait();
                            $this->info('✅ Email completed successfully');
                            $this->info('Response status: ' . $result->getStatusCode());
                        } catch (\Exception $e) {
                            $this->error('❌ Email failed during execution: ' . $e->getMessage());
                        }
                    }
                } else {
                    $this->warn('⚠️  Email returned null (check logs for errors)');
                }
                
            } else {
                $this->info('📤 Sending email synchronously...');
                
                $result = $this->supabaseService->sendStatusEmail(
                    $email,
                    $name,
                    $status,
                    $reason
                );
                
                $this->info('✅ Email sent successfully');
                $this->info('Response: ' . json_encode($result, JSON_PRETTY_PRINT));
            }

        } catch (\Exception $e) {
            $this->error('❌ Email sending failed: ' . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info('📋 Next Steps:');
        $this->line('• Check the recipient\'s email inbox (and spam folder)');
        $this->line('• Monitor Supabase function logs: supabase functions logs send-status-email');
        $this->line('• Check Laravel logs: tail -f storage/logs/laravel.log');

        return 0;
    }
}