<?php
/**
 * SMTP Email Testing Script
 * Run with: php test_mail.php
 * This script tests the Gmail SMTP configuration
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

echo "=== SSG Management System - SMTP Test ===\n\n";

// Display current configuration
echo "📧 Current Mail Configuration:\n";
$config = config('mail');
echo "   Default Mailer: " . $config['default'] . "\n";
echo "   SMTP Host: " . config('mail.mailers.smtp.host') . "\n";
echo "   SMTP Port: " . config('mail.mailers.smtp.port') . "\n";
echo "   SMTP Username: " . config('mail.mailers.smtp.username') . "\n";
echo "   Encryption: " . config('mail.mailers.smtp.encryption') . "\n";
echo "   From Address: " . config('mail.from.address') . "\n";
echo "   From Name: " . config('mail.from.name') . "\n\n";

// Test SMTP connection
echo "🔍 Testing SMTP Connection...\n";
try {
    // Send a test email
    Mail::raw('Test Email from SSG System - SMTP Configuration Test', function (Message $message) {
        $message->to('cpsuhinobaan.ssg.office@gmail.com')
                ->subject('SSG System - SMTP Configuration Test');
    });

    echo "✅ Test email queued successfully!\n";
    echo "   To: cpsuhinobaan.ssg.office@gmail.com\n";
    echo "   Subject: SSG System - SMTP Configuration Test\n\n";
    echo "📌 Note: Email will be sent based on QUEUE_CONNECTION setting.\n";
    echo "   Current: QUEUE_CONNECTION=" . env('QUEUE_CONNECTION') . "\n\n";

} catch (\Exception $e) {
    echo "❌ SMTP Test Failed!\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
    echo "🔧 Troubleshooting:\n";
    echo "   1. Verify MAIL_USERNAME and MAIL_PASSWORD in .env\n";
    echo "   2. Ensure Gmail Account has 2FA enabled\n";
    echo "   3. Create an App Password at: https://myaccount.google.com/apppasswords\n";
    echo "   4. Use App Password in .env (NOT your Gmail password)\n";
    echo "   5. Check firewall allows port 587\n";
}

echo "\n✨ Test complete!\n";
?>
