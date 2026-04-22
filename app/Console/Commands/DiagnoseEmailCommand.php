<?php

namespace App\Console\Commands;

use App\Utilities\EmailDebugger;
use Illuminate\Console\Command;

class DiagnoseEmailCommand extends Command
{
    protected $signature = 'email:diagnose {--test-email=}';

    protected $description = 'Diagnose email/SMTP configuration and help troubleshoot issues';

    public function handle()
    {
        $this->info('🔍 Running Email Diagnostics...\n');

        // Show formatted report
        $this->output->write(EmailDebugger::getFormattedReport());

        // Test email if provided
        if ($testEmail = $this->option('test-email')) {
            $this->info("\n📬 Attempting to send test email to: {$testEmail}\n");

            $result = EmailDebugger::testEmailSend($testEmail);

            if ($result['success']) {
                $this->info('✅ ' . $result['message']);
                if (isset($result['note'])) {
                    $this->line("\n📌 " . $result['note']);
                }
            } else {
                $this->error('❌ Test email failed: ' . $result['error']);

                if (isset($result['diagnosis'])) {
                    $this->line("\n🔧 DIAGNOSIS:");
                    $this->line("   Error Code: " . ($result['diagnosis']['error_code'] ?? 'Unknown'));
                    $this->line("   " . $result['diagnosis']['diagnosis']);

                    if (!empty($result['diagnosis']['causes'])) {
                        $this->line("\n📋 POSSIBLE CAUSES:");
                        foreach ($result['diagnosis']['causes'] as $cause) {
                            $this->line("   • " . $cause);
                        }
                    }

                    if (!empty($result['diagnosis']['fixes'])) {
                        $this->line("\n🔨 FIXES TO TRY:");
                        foreach ($result['diagnosis']['fixes'] as $fix) {
                            $this->line("   • " . $fix);
                        }
                    }
                }
            }
        }

        $this->line("\n✨ Diagnostics complete!");

        return Command::SUCCESS;
    }
}
