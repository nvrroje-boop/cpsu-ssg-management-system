# SSG Management System - Advanced Notification System Guide

## Overview

The Advanced Notification System enables administrators to send targeted announcements to students via email with comprehensive delivery tracking, scheduling, and error handling. The system is built on Laravel's queue system with Gmail SMTP integration.

## Architecture

### Database Schema

#### `announcements` Table

Stores core announcement information with delivery tracking.

```sql
CREATE TABLE announcements (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    created_by_user_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message LONGTEXT NOT NULL,
    target_filters JSON,
    send_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    status ENUM('draft', 'scheduled', 'sent', 'failed'),
    total_recipients INT DEFAULT 0,
    sent_count INT DEFAULT 0,
    failed_count INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id),
);
```

**Column Descriptions:**

- `title`: Announcement headline shown in email subject
- `message`: Full email content with message body
- `target_filters`: JSON object with filtering criteria (course, year, department_id, section_id)
- `send_at`: Scheduled send time (can be NULL for immediate sends)
- `sent_at`: Timestamp when announcement was queued/sent
- `status`: Current state (draft → scheduled/sent or failed)
- `total_recipients`: Count of students eligible for this announcement
- `sent_count`: Number successfully delivered
- `failed_count`: Number that failed after max retries

#### `notifications` Table

Tracks individual email delivery status per student.

```sql
CREATE TABLE notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    announcement_id BIGINT NOT NULL,
    student_id BIGINT NOT NULL,
    status ENUM('queued', 'sent', 'failed', 'bounced'),
    email VARCHAR(255),
    error_message TEXT NULL,
    sent_at TIMESTAMP NULL,
    retry_count INT DEFAULT 0,
    last_attempt_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_notification (announcement_id, student_id),
    FOREIGN KEY (announcement_id) REFERENCES announcements(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
);
```

**Column Descriptions:**

- `status`: Delivery status (queued, sent, failed, bounced)
- `error_message`: Last error reported (e.g., "Invalid email format", "SMTP timeout")
- `retry_count`: Number of send attempts (max 3)
- `last_attempt_at`: When the last send attempt was made
- `unique_notification`: Prevents duplicate notifications for same student+announcement

### Core Models

#### Announcement Model

**Location:** `app/Models/Announcement.php`

**Key Methods:**

```php
// Get notifications for this announcement
$announcement->notifications() // HasMany relationship

// Get target students based on filters
$students = $announcement->getTargetStudents()->get();

// Get delivery statistics
$stats = $announcement->getStats(); // Returns:
// [
//   'total' => 500,
//   'sent' => 450,
//   'failed' => 30,
//   'queued' => 20
// ]
```

**Relationships:**

- `creator()` - BelongsTo User (created_by_user_id)
- `notifications()` - HasMany Notification
- `department()` - BelongsTo Department (optional)

#### Notification Model

**Location:** `app/Models/Notification.php`

**Key Methods:**

```php
// Mark email as successfully sent
$notification->markAsSent();

// Mark email as failed with error
$notification->markAsFailed('SMTP error: Connection timeout');

// Check if notification can be retried
if ($notification->shouldRetry()) {
    // Retry count < 3 and status is 'failed'
}
```

### Queue Job

#### SendAnnouncementJob

**Location:** `app/Jobs/SendAnnouncementJob.php`

**Configuration:**

- `$tries = 3`: Maximum 3 retry attempts
- `$backoff = [30, 60, 120]`: Wait 30s, then 60s, then 120s before retries
- `$timeout = 120`: Job must complete within 120 seconds
- Random delay (0-5s) per job for rate limiting

**Dispatch:**

```php
foreach ($targetStudents as $student) {
    SendAnnouncementJob::dispatch($announcement, $student);
}
```

**Process Flow:**

1. Retrieve notification record for student
2. Validate email address
3. Send email via Mail::to()->send(AnnouncementMail)
4. Update notification status (sent/failed)
5. Update announcement counters
6. On final failure: Log error, mark notification as failed

### Email Template

**Location:** `resources/views/emails/announcement-notification.blade.php`

Renders a professional HTML email with:

- Personalized greeting with student name
- Announcement title and message
- Call-to-action button (optional)
- SSG branding and footer

### Scheduler

**Location:** `app/Console/Kernel.php`

**Scheduled Tasks:**

1. **Every Minute** - Process scheduled announcements:

```php
$schedule->call(function () {
    // Find announcements with send_at <= now() that haven't been sent
    // Create notification records if not exist
    // Dispatch SendAnnouncementJob for each student
    // Mark announcement as sent
})->everyMinute();
```

2. **Daily** - Clean up old logs:

```php
Notification::where('created_at', '<', now()->subMonths(3))->delete();
```

**Start Scheduler:**

```bash
php artisan schedule:work
# Or add to crontab:
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

### Admin Controller

**Location:** `app/Http/Controllers/Admin/AnnouncementController.php`

**Key Actions:**

| Action             | Route                                          | Purpose                             |
| ------------------ | ---------------------------------------------- | ----------------------------------- |
| `index`            | GET `/admin/announcements`                     | List all announcements with stats   |
| `create`           | GET `/admin/announcements/create`              | Show creation form                  |
| `store`            | POST `/admin/announcements`                    | Create announcement as draft        |
| `show`             | GET `/admin/announcements/{id}`                | Show details + delivery dashboard   |
| `send`             | POST `/admin/announcements/{id}/send`          | Send immediately or schedule        |
| `resendFailed`     | POST `/admin/announcements/{id}/resend-failed` | Retry failed notifications          |
| `getTargetPreview` | POST `/admin/announcements/target-preview`     | AJAX endpoint for recipient preview |

---

## Usage Workflow

### Creating an Announcement

1. Navigate to **Admin → Announcements → Create Announcement**
2. Fill in:
    - **Title**: Announcement headline
    - **Message**: Full email content
    - **Target Filters**: (optional) Course, Year, Department, Section
    - **Send Type**: Now or Scheduled
3. Click "Create Draft"
4. Announcement is saved with status `draft`

### Previewing Recipients

1. From the creation form, click **"Preview"** button
2. System fetches matching students based on filters
3. Shows count and list of recipients
4. Allows refinement before sending

### Sending an Announcement

**Immediate Send:**

1. From draft announcement, click **"Send Now"**
2. Confirm recipient count
3. Click **"Confirm & Send"**
4. Process:
    - System identifies target students
    - Creates notification records
    - Dispatches SendAnnouncementJob for each
    - Jobs queued for processing

**Scheduled Send:**

1. From creation form, select **"Schedule for Later"**
2. Set send date/time (future)
3. System stores announcement with status `scheduled`
4. Scheduler runs every minute:
    - Finds announcements with `send_at <= now()`
    - Processes if `sent_at` is NULL
    - Dispatches jobs exactly at scheduled time

### Monitoring Delivery

1. Go to **Admin → Announcements** (index)
2. View announcement status and delivery stats:
    - **Status Badge**: draft/scheduled/sent/failed
    - **Delivery Progress**: Visual progress bar
    - **Statistics**: X/Total sent, Y failed
3. Click to view announcement **Details**
4. Detailed dashboard shows:
    - **Statistics Cards**: Total, Sent, Queued, Failed
    - **Delivery Table**: Per-student status with attempt count
    - **Filter by Status**: View only queued, sent, failed, or bounced

### Handling Failures

**Auto-Retry:**

- Jobs automatically retry 3 times with exponential backoff
- First failure: retry in 30 seconds
- Second failure: retry in 60 seconds
- Third failure: marked as failed permanently

**Manual Resend:**

1. View announcement details
2. Click **"Retry Failed (X)"** button
3. Confirm action
4. Failed notifications with retry_count < 3 requeued
5. Jobs dispatched again to queue

---

## Gmail SMTP Configuration

**File:** `.env`

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@ssg.system
MAIL_FROM_NAME="SSG Management System"

# Queue Configuration
QUEUE_CONNECTION=database
```

**Gmail App Password Setup:**

1. Enable 2-Factor Authentication on Gmail
2. Generate App Password: https://myaccount.google.com/apppasswords
3. Use generated password in `MAIL_PASSWORD`

**Rate Limits:**

- ~100 emails per 10 minutes
- System implements delays to stay under limit
- Exponential backoff prevents account throttling

---

## Running Queue Worker

Queue worker processes SendAnnouncementJob instances.

```bash
# Start queue worker with high verbosity
php artisan queue:work database --tries=3 --timeout=120 --verbose

# Or use Supervisor for persistent background process
# See deployment guide for Supervisor setup
```

**Monitoring:**

```bash
# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

---

## Debugging & Monitoring

### Database Setup

Before first use, run migrations:

```bash
php artisan migrate
```

### Verifying Installation

Check that tables exist:

```bash
php artisan migrate:status
```

### Testing Email Sending

Send test announcement through admin panel:

1. Create new announcement
2. Add specific department/course as filter
3. Preview to verify filters work
4. Send to small group first
5. Check notifications table for status

### Logs

**Location:** `storage/logs/laravel.log`

**Key Events:**

- `Scheduled announcement sent to queue`
- `Failed to send scheduled announcement`
- `Email delivery failed: xyz@example.com`
- Queue worker processing logs

### Console Commands

**Diagnostic:**

```bash
# Check email configuration
php artisan email:diagnose

# View email statistics
php artisan email:stats

# View announcement statistics
php artisan notification:stats
```

---

## Troubleshooting

### Issue: Announcements not being queued

**Symptoms:** Send button works but no emails sent

**Solutions:**

1. Check queue worker is running: `ps aux | grep artisan`
2. Verify queue configuration: `php artisan config:cache && php artisan cache:clear`
3. Check jobs table: `SELECT COUNT(*) FROM jobs;`
4. Look at logs: `tail -f storage/logs/laravel.log`

### Issue: Scheduled announcements not sending

**Symptoms:** Status remains "scheduled" after send_at time

**Solutions:**

1. Verify scheduler running: `php artisan schedule:work`
2. Check cron job if in production: `crontab -l`
3. Verify send_at is in correct format (datetime)
4. Check that `sent_at` is NULL (indicates not yet sent)
5. Review logs for scheduler errors

### Issue: SMTP authentication errors

**Symptoms:** Emails showing "failed" status with SMTP error

**Solutions:**

1. Verify Gmail App Password (not regular password)
2. Check MAIL_PORT is 587 (not 25 or 465)
3. Confirm MAIL_ENCRYPTION is 'tls'
4. Test connection: `php artisan email:diagnose`
5. Check Gmail account "Less secure apps" settings

### Issue: High failure rate

**Symptoms:** Many notifications with "failed" status

**Solutions:**

1. Check email validity: `SELECT COUNT(*) FROM notifications WHERE email IS NULL;`
2. Review error_message field for patterns
3. Check student records have valid emails
4. Verify SMTP rate limits not exceeded
5. Look for firewall/network issues in logs

---

## Performance Optimization

### Chunking Large Sends

For 10,000+ recipients, use chunking:

```php
$announcement->getTargetStudents()->chunk(100, function ($students) use ($announcement) {
    foreach ($students as $student) {
        SendAnnouncementJob::dispatch($announcement, $student);
    }
});
```

### Bulk Database Inserts

When creating many notifications:

```php
$notificationsData = $targetStudents->map(fn($student) => [
    'announcement_id' => $announcement->id,
    'student_id' => $student->id,
    'email' => $student->email,
    'status' => 'queued',
    'created_at' => now(),
    'updated_at' => now(),
])->toArray();

Notification::insert($notificationsData); // Bulk insert much faster
```

### Queue Configuration

**File:** `config/queue.php`

```php
'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'expire' => 86400,
        'retry_after' => 360, // Retry job if still running after 360 seconds
    ],
],
```

### Supervisor Configuration

For production, use Supervisor to automatically restart queue worker:

```ini
[program:ssg-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/user/ssg-management-system/artisan queue:work database --sleep=3 --tries=3 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
stopwaitsecs=120
numprocs=4
redirect_stderr=true
stdout_logfile=/home/user/ssg-management-system/storage/logs/worker.log
```

---

## API Endpoints

Admin dashboard uses these endpoints:

```
POST /admin/announcements/target-preview
- Request: { course?: string, year?: string, department_id?: id, section_id?: id }
- Response: { count: int, students: array }
- Usage: Real-time recipient count and preview

POST /admin/announcements/{id}/send
- Request: { send_type: 'now'|'scheduled', send_at?: datetime }
- Response: Redirects to show view with success message

POST /admin/announcements/{id}/resend-failed
- Request: None (uses POST method)
- Response: Redirects with success message and count
```

---

## Best Practices

1. **Test First**: Always preview recipients before sending to large groups
2. **Schedule Off-Hours**: Send bulk announcements during low-traffic times
3. **Monitor Worker**: Ensure queue worker stays running in production
4. **Review Logs**: Regularly check for SMTP errors or timeout issues
5. **Backup Database**: Critical notification records are permanent
6. **Update Email Lists**: Keep student email addresses current
7. **Use Filters**: Target specific groups to reduce unnecessary emails
8. **Document Content**: Note any important announcements in system

---

## Migration & Deployment

### Initial Setup

```bash
# 1. Pull latest code
git pull origin main

# 2. Install/update composer dependencies
composer install --no-dev --optimize-autoloader

# 3. Set environment variables
cp .env.example .env
# Edit .env with Gmail credentials, database, etc.

# 4. Generate application key
php artisan key:generate

# 5. Run migrations
php artisan migrate --force

# 6. Cache configuration
php artisan config:cache
php artisan route:cache

# 7. Start queue worker (in supervisor or screen)
supervisorctl restart ssg-queue-worker
```

### Post-Deployment

```bash
# Clear any cached config conflicting with new tables
php artisan cache:clear

# Verify tables created
php artisan migrate:status

# Test email system
php artisan email:diagnose
```

---

## Support & Resources

**Files Reference:**

- Models: `app/Models/Announcement.php`, `app/Models/Notification.php`
- Jobs: `app/Jobs/SendAnnouncementJob.php`
- Controller: `app/Http/Controllers/Admin/AnnouncementController.php`
- Views: `resources/views/admin/announcements/`
- Email Template: `resources/views/emails/announcement-notification.blade.php`
- Migrations: `database/migrations/2026_03_31_*.php`
- Scheduler: `app/Console/Kernel.php`

**Related Documentation:**

- Gmail SMTP: `PRODUCTION_SUMMARY.md`
- Email System: See previous email setup guide
- Deployment: `DEPLOYMENT_GUIDE.md`

---

## Version Information

- **Laravel**: 12
- **PHP**: 8.2+
- **Database**: MySQL 8.0+
- **Queue Driver**: Database (Redis recommended for production)
- **Last Updated**: 2026-03-31

---

## Changelog

### Current Release

- ✅ Advanced notification system with targeting and scheduling
- ✅ Error handling with exponential backoff retries
- ✅ Admin dashboard with delivery tracking
- ✅ Scheduled announcement delivery
- ✅ Rate limiting for Gmail SMTP
- ✅ Bulk notification creation and processing
- ✅ Failed notification resend capability
- ✅ Real-time recipient previewing
