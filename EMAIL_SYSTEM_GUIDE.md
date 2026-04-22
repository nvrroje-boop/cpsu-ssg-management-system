# 📧 SSG Management System - Complete Email & Bulk Notification Guide

## Table of Contents

1. [Configuration Overview](#configuration-overview)
2. [Queue System Setup](#queue-system-setup)
3. [Email Sending Implementation](#email-sending-implementation)
4. [Debugging & Troubleshooting](#debugging--troubleshooting)
5. [Production Best Practices](#production-best-practices)
6. [Testing Guide](#testing-guide)

---

## Configuration Overview

### ✅ Current Setup

Your system is configured with:

- **Mail Driver**: SMTP
- **SMTP Host**: smtp.gmail.com
- **SMTP Port**: 587 (TLS)
- **Queue Connection**: database
- **Email Logging**: Enabled with tracking

### 📋 Required Environment Variables

```dotenv
# Mail Configuration
MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=cpsuhinobaan.ssg.office@gmail.com
MAIL_PASSWORD=osxuqnsbkozcnihc
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=cpsuhinobaan.ssg.office@gmail.com
MAIL_FROM_NAME="CPSU Hinoba-an SSG"

# Queue Configuration
QUEUE_CONNECTION=database
```

### ⚠️ Important: Gmail App Password

**DO NOT use your regular Gmail password!**

1. Enable 2-Factor Authentication in Google Account
2. Go to https://myaccount.google.com/apppasswords
3. Create a new "App Password" (16 characters)
4. Use this password in MAIL_PASSWORD

---

## Queue System Setup

### Why Use Queues?

- **Performance**: Emails don't block user requests
- **Scalability**: Handle thousands of emails without crashing
- **Reliability**: Failed emails automatically retry
- **Monitoring**: Track email delivery status

### Current Configuration

```php
// config/queue.php
QUEUE_CONNECTION=database  // Emails stored in DB, processed by worker
```

### How to Run

```bash
# Start the queue worker (keeps running)
php artisan queue:work

# Run specific number of jobs then exit
php artisan queue:work --max-jobs=50

# Run once
php artisan queue:work --once

# Process specific queue
php artisan queue:work --queue=announcements

# View job status
php artisan queue:failed
```

### For Development

```bash
# Run in foreground with verbose output
php artisan queue:work --verbose

# Kill stuck jobs
php artisan queue:flush
```

---

## Email Sending Implementation

### 1. Send Announcement to All Students

**Service Class** (Handles everything efficiently)

```php
<?php
use App\Services\BulkEmailService;
use App\Models\Announcement;

$announcement = Announcement::find(1);
$service = new BulkEmailService();

$stats = $service->sendAnnouncementBulk($announcement);

// Returns: ['success' => 500, 'failed' => 2, 'total' => 502]
```

### 2. Send Event to All Students

```php
<?php
use App\Services\BulkEmailService;
use App\Models\Event;

$event = Event::find(1);
$service = new BulkEmailService();

$stats = $service->sendEventBulk($event);
```

### 3. Send to Specific Students

```php
<?php
use App\Services\BulkEmailService;

$studentIds = [1, 5, 12, 25]; // Array of user IDs
$announcement = Announcement::find(1);
$service = new BulkEmailService();

$stats = $service->sendAnnouncementBulk($announcement, $studentIds);
```

### In Controllers

```php
<?php
namespace App\Http\Controllers\Admin;

use App\Services\BulkEmailService;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function store(AnnouncementRequest $request)
    {
        $announcement = Announcement::create($request->validated());

        // Send emails asynchronously
        $emailService = new BulkEmailService();
        $stats = $emailService->sendAnnouncementBulk($announcement);

        return back()->with('success',
            "Announcement created and {$stats['success']} emails queued!");
    }
}
```

### Mailable Classes

#### AnnouncementMail

```php
use App\Mail\AnnouncementMail;
use Illuminate\Support\Facades\Mail;

Mail::to($student->email)
    ->queue(new AnnouncementMail($announcement, $student->name));
```

#### EventMail

```php
use App\Mail\EventMail;
use Illuminate\Support\Facades\Mail;

Mail::to($student->email)
    ->queue(new EventMail($event, $student->name));
```

### Email Queue Flow

```
Controller Request
    ↓
BulkEmailService::sendAnnouncementBulk()
    ↓
Queue Chunks (50 students at a time)
    ↓
Mail::queue() → Jobs Table
    ↓
Queue Worker Processes Jobs
    ↓
SMTP Sends Email
    ↓
EmailLog Created
    ↓
✅ Student Receives Email
```

---

## Debugging & Troubleshooting

### 1. Diagnose Email System

```bash
# Full system diagnostics
php artisan email:diagnose

# Test send email
php artisan email:diagnose --test-email=your@email.com
```

**Output includes:**

- Configuration verification
- SMTP connectivity test
- Credentials validation
- Recommendations

### 2. Common Issues & Fixes

#### ❌ Error: "535 Authentication failed"

**Cause**: Invalid credentials

**Fix**:

- Verify email in .env matches Gmail account
- Generate new App Password (NOT regular password)
- Clear config cache: `php artisan config:clear && php artisan config:cache`

#### ❌ Error: "421 Service temporarily unavailable"

**Cause**: Connection timeout or firewall blocking

**Fix**:

```bash
# Test connection
telnet smtp.gmail.com 587

# If fails: Check firewall for port 587
# Contact IT if blocked
```

#### ❌ Emails Going to Spam

**Cause**: Gmail thinks sender is suspicious

**Fix**:

- Add SPF/DKIM records to domain
- Send from consistent sender
- Start with small batches (warm up)
- Use professional email template (already included)

#### ❌ Queue Jobs Not Processing

**Cause**: Queue worker not running

**Fix**:

```bash
# Make sure worker is running
ps aux | grep queue:work

# Start new worker
php artisan queue:work

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### 3. View Email Logs

```bash
# Show statistics for last 30 days
php artisan email:stats

# Last 60 days
php artisan email:stats --days=60
```

### 4. Database Query for Email History

```php
<?php
use App\Models\EmailLog;

// All emails
$logs = EmailLog::all();

// Emails to specific student
$logs = EmailLog::where('user_id', 1)->get();

// Failed emails
$failures = EmailLog::where('status', 'failed')->get();

// Announcements sent this month
$announcements = EmailLog::whereMonth('sent_at', now()->month)
    ->where('email_type', 'announcement')
    ->count();

// Emails with retry attempts
$retried = EmailLog::where('retry_count', '>', 0)->get();
```

### 5. Manual Email Sending (for testing)

```bash
php artisan tinker

# Then run:
use App\Models\User, App\Mail\AnnouncementMail;
$student = User::find(1);
$announcement = \App\Models\Announcement::find(1);
Mail::to($student->email)->send(new AnnouncementMail($announcement, $student->name));
```

---

## Production Best Practices

### ✅ DO's

1. **Always use queues** for bulk emails

    ```php
    Mail::to($email)->queue(new AnnouncementMail(...));  // ✅ Good
    Mail::to($email)->send(new AnnouncementMail(...));   // ❌ Avoid
    ```

2. **Use environment variables** for credentials

    ```php
    // ✅ Good
    config('mail.mailers.smtp.username')

    // ❌ Never hardcode
    'username' => 'cpsuhinobaan.ssg.office@gmail.com'
    ```

3. **Monitor queue workers**
    - Use process manager (Supervisor, systemd)
    - Keep worker running 24/7
    - Auto-restart on failure

4. **Chunk large email operations**

    ```php
    User::chunk(100, function ($students) {
        foreach ($students as $student) {
            Mail::to($student->email)->queue(...);
        }
    });
    ```

5. **Log important events**
    - Use BulkEmailService::sendAnnouncementBulk() - already logs
    - Check storage/logs/laravel.log

6. **Test in staging first**
    - Test with small student groups
    - Verify email formatting
    - Check spam folder

### ❌ DON'Ts

1. **Don't send synchronously** (blocks requests)
2. **Don't hardcode credentials** in code
3. **Don't send massive batches** without chunking
4. **Don't ignore failed emails** - monitor them
5. **Don't change SMTP settings** without testing first

### 🔒 Security Checklist

- [ ] App Password created (not regular password)
- [ ] 2FA enabled on Gmail account
- [ ] .env not committed to git
- [ ] Don't share .env file with team
- [ ] Rotate passwords quarterly
- [ ] Monitor unusual email patterns
- [ ] Restrict database access

---

## Testing Guide

### Unit Test Example

```php
<?php
namespace Tests\Feature;

use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\User;
use App\Services\BulkEmailService;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailTest extends TestCase
{
    public function test_announcement_email_can_be_sent()
    {
        Mail::fake();

        $student = User::factory()->create(['role_id' => 3]);
        $announcement = Announcement::factory()->create();

        Mail::to($student->email)->send(
            new AnnouncementMail($announcement, $student->name)
        );

        Mail::assertSent(AnnouncementMail::class);
    }

    public function test_bulk_announcement_sends_to_all_students()
    {
        Mail::fake();

        $students = User::factory(50)->create(['role_id' => 3]);
        $announcement = Announcement::factory()->create();

        $service = new BulkEmailService();
        $stats = $service->sendAnnouncementBulk($announcement);

        Mail::assertSentCount(50);
        $this->assertEquals(50, $stats['success']);
    }
}
```

### Manual Testing Steps

1. **Create test account**

    ```php
    $testStudent = User::create([
        'name' => 'Test Student',
        'email' => 'test@gmail.com',
        'role_id' => 3,
        'password' => bcrypt('password'),
    ]);
    ```

2. **Send test email**

    ```php
    php artisan email:diagnose --test-email=test@gmail.com
    ```

3. **Check email received**
    - Verify email arrives within 30 seconds
    - Check formatting
    - Click links
    - Check spam folder

4. **Verify in database**

    ```php
    EmailLog::where('email', 'test@gmail.com')->latest()->first();
    ```

5. **Test bulk send**

    ```bash
    php artisan tinker

    $announcement = \App\Models\Announcement::first();
    $service = new \App\Services\BulkEmailService();
    $stats = $service->sendAnnouncementBulk($announcement);
    // Check returned stats
    ```

---

## Command Reference

```bash
# Email System
php artisan email:diagnose              # Full diagnostics
php artisan email:diagnose --test-email=user@gmail.com  # With test send
php artisan email:stats                 # Last 30 days stats
php artisan email:stats --days=60       # Custom period

# Queue Management
php artisan queue:work                  # Start queue worker
php artisan queue:work --verbose        # With verbose logging
php artisan queue:retry all             # Retry failed jobs
php artisan queue:flush                 # Clear all jobs
php artisan queue:failed                # View failed jobs

# Configuration
php artisan config:cache                # Cache config
php artisan config:clear                # Clear config cache
php artisan migrate                     # Run migrations

# Development
php artisan tinker                      # PHP shell
php artisan make:mail NewMail           # Create new mailable
php artisan list                        # Show all commands
```

---

## Architecture Summary

### Email System Components

```
┌─────────────────────────────────────────────────┐
│          Web Request / Artisan Command          │
└──────────────────┬──────────────────────────────┘
                   │
                   ↓
        ┌──────────────────────┐
        │  BulkEmailService    │ ← Main Service
        ├──────────────────────┤
        │ sendAnnouncementBulk  │
        │ sendEventBulk         │
        │ sendToStudent         │
        │ getEmailStats         │
        └──────────┬────────────┘
                   │
     ┌─────────────┼─────────────┐
     ↓             ↓             ↓
┌──────────┐  ┌─────────────┐  ┌──────────┐
│ Mailable │  │  Queue      │  │ EmailLog │
│ Classes  │  │  (Database) │  │ Model    │
└──────────┘  └─────────────┘  └──────────┘
     │             │
     └─────────────┼─────────────┐
                   ↓             ↓
            ┌────────────┐   ┌──────────────┐
            │ Queue      │   │ EmailDebugger│
            │ Worker     │   │ Utility      │
            └─────┬──────┘   └──────────────┘
                  │
                  ↓
           ┌────────────────┐
           │ SMTP (Gmail)   │
           └────────────────┘
```

### Email Template System

```
layout.blade.php (Master Template)
    ├── announcement.blade.php
    ├── event.blade.php
    └── [Future emails can extend]
```

---

## Monitoring & Maintenance

### Daily Tasks

- [ ] Monitor queue worker status
- [ ] Check failed email count
- [ ] Review error logs

### Weekly Tasks

- [ ] Generate email statistics (`php artisan email:stats`)
- [ ] Test send sample email
- [ ] Clear old logs if needed

### Monthly Tasks

- [ ] Review email performance trends
- [ ] Rotate Gmail App Password
- [ ] Update documentation if needed

---

## Support & Troubleshooting

### Quick Start

1. **First time?** Run: `php artisan email:diagnose`
2. **Email not sending?** Run: `php artisan queue:work` (check console output)
3. **Need help?** Check email logs: `php artisan email:stats`

### Emergency Procedures

**Queue backed up?**

```bash
php artisan queue:flush           # Clear stuck jobs (careful!)
php artisan queue:work --once     # Process once to check
```

**Gmail account locked?**

- Log into Gmail directly
- Verify suspicious activity
- Generate new App Password
- Update .env

**Still stuck?**

- Check storage/logs/laravel.log
- Run full diagnostics: `php artisan email:diagnose`
- Contact system administrator

---

## Next Steps

1. ✅ Configuration validated
2. ✅ Queue system ready
3. ✅ Email templates created
4. ✅ Bulk email service implemented
5. ✅ Debugging tools available
6. 🔄 **Recommended**: Start queue worker in production
7. 🔄 **Recommended**: Set up monitoring/alerting
8. 🔄 **Recommended**: Create backup Gmail account

Happy emailing! 📧✨
