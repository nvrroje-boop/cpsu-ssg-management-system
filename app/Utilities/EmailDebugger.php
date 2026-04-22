<?php

namespace App\Utilities;

use Illuminate\Support\Facades\Log;
use Swift_SmtpTransport;

/**
 * Email Debugging Utility
 * Diagnoses and helps fix SMTP email issues
 * Provides detailed error analysis and solutions
 */
class EmailDebugger
{
    /**
     * Common SMTP error codes and diagnoses
     */
    private const ERROR_DIAGNOSTICS = [
        '530' => [
            'error' => 'Authentication failed',
            'causes' => [
                '1. Incorrect MAIL_USERNAME or MAIL_PASSWORD',
                '2. Gmail App Password not created (required if 2FA enabled)',
                '3. Email credentials don\'t match Gmail account',
            ],
            'fixes' => [
                '- Verify credentials in .env file',
                '- If Gmail 2FA is enabled: Create App Password at https://myaccount.google.com/apppasswords',
                '- Use 16-character App Password, not regular Gmail password',
                '- Test in Gmail directly first',
            ],
        ],
        '535' => [
            'error' => 'Authentication credentials invalid',
            'causes' => [
                '1. Wrong App Password format',
                '2. Gmail account changed',
                '3. Credentials expired',
            ],
            'fixes' => [
                '- Generate new App Password from Google Account settings',
                '- Ensure password has no spaces',
                '- Clear config cache: php artisan config:clear && php artisan config:cache',
            ],
        ],
        '421' => [
            'error' => 'Service temporarily unavailable / Connection timeout',
            'causes' => [
                '1. Firewall blocking port 587',
                '2. Gmail server temporarily down',
                '3. Network connectivity issue',
                '4. Connection timeout too short',
            ],
            'fixes' => [
                '- Check firewall allows outbound port 587',
                '- Test connection: telnet smtp.gmail.com 587',
                '- Check internet connectivity',
                '- Increase timeout in config/mail.php',
            ],
        ],
        '550' => [
            'error' => 'Mailbox not found / Invalid recipient',
            'causes' => [
                '1. Recipient email address doesn\'t exist',
                '2. Email marked as spam sender',
                '3. Gmail treating sender as suspicious',
            ],
            'fixes' => [
                '- Verify recipient email address exists',
                '- Add SPF/DKIM records for domain',
                '- Use consistent sender name',
                '- Warm up sending: start with small batches',
            ],
        ],
        '552' => [
            'error' => 'Message rejected - exceeds size limits',
            'causes' => [
                '1. Email body too large',
                '2. Attachments too large',
                '3. Gmail size limits exceeded',
            ],
            'fixes' => [
                '- Gmail limit: 40MB per email',
                '- Remove large attachments',
                '- Compress images in email template',
                '- Send in multiple emails if needed',
            ],
        ],
    ];

    /**
     * Run comprehensive SMTP diagnostics
     *
     * @return array Detailed diagnostic report
     */
    public static function diagnoseSmtp(): array
    {
        $report = [
            'timestamp' => now()->toIso8601String(),
            'configuration' => self::checkConfiguration(),
            'connectivity' => self::checkConnectivity(),
            'credentials' => self::checkCredentials(),
            'recommendations' => self::getRecommendations(),
        ];

        Log::info('SMTP Diagnostics Report', $report);

        return $report;
    }

    /**
     * Check SMTP configuration
     *
     * @return array
     */
    private static function checkConfiguration(): array
    {
        $config = config('mail');

        return [
            'default_mailer' => $config['default'] ?? 'NOT SET',
            'status' => ($config['default'] === 'smtp') ? 'CONFIGURED' : 'NOT CONFIGURED',
            'smtp_host' => $config['mailers']['smtp']['host'] ?? 'NOT SET',
            'smtp_port' => $config['mailers']['smtp']['port'] ?? 'NOT SET',
            'smtp_encryption' => $config['mailers']['smtp']['encryption'] ?? 'NOT SET',
            'from_address' => $config['from']['address'] ?? 'NOT SET',
            'from_name' => $config['from']['name'] ?? 'NOT SET',
            'queue_connection' => config('queue.default') ?? 'NOT SET',
        ];
    }

    /**
     * Check SMTP connectivity
     *
     * @return array
     */
    private static function checkConnectivity(): array
    {
        $host = config('mail.mailers.smtp.host');
        $port = config('mail.mailers.smtp.port');

        $result = [
            'host' => $host,
            'port' => $port,
            'connection' => 'CHECKING...',
            'latency_ms' => null,
        ];

        // Check DNS resolution
        $ip = gethostbyname($host);
        if ($ip === $host) {
            $result['dns'] = 'FAILED - Host not resolving';

            return $result;
        }

        $result['dns'] = 'OK - ' . $ip;

        // Try connection
        $startTime = microtime(true);
        $connection = @fsockopen($host, $port, $errno, $errstr, 5);

        if ($connection) {
            $result['connection'] = 'OK';
            $result['latency_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            fclose($connection);
        } else {
            $result['connection'] = 'FAILED - ' . $errstr . ' (Error: ' . $errno . ')';
            $result['solution'] = 'Check firewall settings for port 587';
        }

        return $result;
    }

    /**
     * Check SMTP credentials
     *
     * @return array
     */
    private static function checkCredentials(): array
    {
        $username = config('mail.mailers.smtp.username');
        $password = config('mail.mailers.smtp.password');

        $result = [
            'username_set' => !empty($username),
            'username' => $username ? substr($username, 0, 5) . '***' . substr($username, -5) : 'NOT SET',
            'password_set' => !empty($password),
            'password_length' => strlen($password ?? ''),
            'status' => 'PENDING VERIFICATION',
        ];

        if (empty($username) || empty($password)) {
            $result['status'] = 'INVALID - Credentials not set in .env';

            return $result;
        }

        if (!str_contains($username, '@gmail.com')) {
            $result['warning'] = 'Username should be Gmail account (xxx@gmail.com)';
        }

        if (strlen($password) < 16) {
            $result['warning'] = 'Gmail password should be 16-character App Password, not regular password';
        }

        return $result;
    }

    /**
     * Get troubleshooting recommendations
     *
     * @return array
     */
    private static function getRecommendations(): array
    {
        $config = config('mail');
        $recommendations = [];

        // Check basic configuration
        if ($config['default'] !== 'smtp') {
            $recommendations[] = '❌ Default mailer is not SMTP. Set MAIL_MAILER=smtp in .env';
        }

        // Check encryption
        $encryption = $config['mailers']['smtp']['encryption'] ?? null;
        if ($encryption !== 'tls' && $encryption !== 'ssl') {
            $recommendations[] = '❌ Encryption should be TLS or SSL. Gmail uses TLS on port 587.';
        }

        // Check port
        $port = $config['mailers']['smtp']['port'] ?? null;
        if ($port == 465) {
            $recommendations[] = '⚠️  Port 465 is for SSL. Consider using port 587 with TLS instead.';
        } elseif ($port != 587) {
            $recommendations[] = '⚠️  Standard Gmail SMTP port is 587 (TLS). Current: ' . $port;
        }

        // Check queue
        if (config('queue.default') === 'sync') {
            $recommendations[] = '⚠️  Queue is set to SYNC. Bulk emails will block requests. Set QUEUE_CONNECTION=database for better performance.';
        }

        if (empty($recommendations)) {
            $recommendations[] = '✅ Configuration looks good!';
        }

        return $recommendations;
    }

    /**
     * Parse SMTP error and provide diagnosis
     *
     * @param string $errorMessage
     * @return array
     */
    public static function diagnoseError(string $errorMessage): array
    {
        // Extract error code
        $matches = [];
        preg_match('/\[(\d{3})\]/', $errorMessage, $matches);
        $errorCode = $matches[1] ?? null;

        $diagnosis = [
            'raw_error' => $errorMessage,
            'error_code' => $errorCode,
            'diagnosis' => null,
            'causes' => [],
            'fixes' => [],
        ];

        if ($errorCode && isset(self::ERROR_DIAGNOSTICS[$errorCode])) {
            $diag = self::ERROR_DIAGNOSTICS[$errorCode];
            $diagnosis['diagnosis'] = $diag['error'];
            $diagnosis['causes'] = $diag['causes'];
            $diagnosis['fixes'] = $diag['fixes'];
        } else {
            $diagnosis['diagnosis'] = 'Unknown SMTP error. See raw error message above.';
            $diagnosis['causes'] = ['Check SMTP server logs', 'Verify credentials', 'Check network connectivity'];
            $diagnosis['fixes'] = [
                '1. Run diagnostics: EmailDebugger::diagnoseSmtp()',
                '2. Check Laravel logs: storage/logs/laravel.log',
                '3. Test Gmail directly in browser',
                '4. Verify App Password instead of regular password',
            ];
        }

        Log::error('SMTP Error Diagnosis', $diagnosis);

        return $diagnosis;
    }

    /**
     * Test email sending
     *
     * @param string $testEmail
     * @return array
     */
    public static function testEmailSend(string $testEmail): array
    {
        try {
            \Illuminate\Support\Facades\Mail::raw('This is a test email from SSG Management System', function ($message) use ($testEmail) {
                $message->to($testEmail)
                        ->subject('SSG System - Email Test');
            });

            return [
                'success' => true,
                'message' => 'Test email sent successfully to ' . $testEmail,
                'note' => 'Check email inbox (or spam folder). If using QUEUE_CONNECTION=sync, it should arrive immediately. If using database queue, start queue worker: php artisan queue:work',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'diagnosis' => self::diagnoseError($e->getMessage()),
            ];
        }
    }

    /**
     * Get formatted diagnostic report for display
     *
     * @return string
     */
    public static function getFormattedReport(): string
    {
        $report = self::diagnoseSmtp();

        $output = "═════════════════════════════════════════════════════════════\n";
        $output .= "       SSG MANAGEMENT SYSTEM - EMAIL DIAGNOSTICS REPORT        \n";
        $output .= "═════════════════════════════════════════════════════════════\n\n";

        $output .= "📧 CONFIGURATION\n";
        $output .= "─────────────────────────────────────────────────────────────\n";
        foreach ($report['configuration'] as $key => $value) {
            $output .= sprintf("  %-25s: %s\n", $key, $value);
        }

        $output .= "\n🔗 CONNECTIVITY\n";
        $output .= "─────────────────────────────────────────────────────────────\n";
        foreach ($report['connectivity'] as $key => $value) {
            $output .= sprintf("  %-25s: %s\n", $key, $value);
        }

        $output .= "\n🔐 CREDENTIALS\n";
        $output .= "─────────────────────────────────────────────────────────────\n";
        foreach ($report['credentials'] as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'YES' : 'NO';
            }
            $output .= sprintf("  %-25s: %s\n", $key, $value);
        }

        $output .= "\n💡 RECOMMENDATIONS\n";
        $output .= "─────────────────────────────────────────────────────────────\n";
        foreach ($report['recommendations'] as $rec) {
            $output .= "  • " . $rec . "\n";
        }

        $output .= "\n═════════════════════════════════════════════════════════════\n";

        return $output;
    }
}
