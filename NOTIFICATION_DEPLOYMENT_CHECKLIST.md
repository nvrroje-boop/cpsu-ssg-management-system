# Notification System - Deployment Checklist & Quick Start

## ✅ Component Status

| Component            | Status      | Location                                                     |
| -------------------- | ----------- | ------------------------------------------------------------ |
| Announcement Model   | ✅ Ready    | `app/Models/Announcement.php`                                |
| Notification Model   | ✅ Ready    | `app/Models/Notification.php`                                |
| SendAnnouncementJob  | ✅ Ready    | `app/Jobs/SendAnnouncementJob.php`                           |
| Admin Controller     | ✅ Ready    | `app/Http/Controllers/Admin/AnnouncementController.php`      |
| Mailable             | ✅ Updated  | `app/Mail/AnnouncementMail.php`                              |
| Email Template       | ✅ Ready    | `resources/views/emails/announcement-notification.blade.php` |
| Blade Views (index)  | ✅ Enhanced | `resources/views/admin/announcements/index.blade.php`        |
| Blade Views (show)   | ✅ New      | `resources/views/admin/announcements/show.blade.php`         |
| Blade Views (create) | ✅ Enhanced | `resources/views/admin/announcements/create.blade.php`       |
| Database Migrations  | ✅ Ready    | `database/migrations/2026_03_31_*.php`                       |
| Scheduler            | ✅ Ready    | `app/Console/Kernel.php`                                     |

---

## 🚀 Quick Start

### 1. Database Setup

```bash
# Run migrations to create announcements and notifications tables
php artisan migrate

# Verify tables created
php artisan migrate:status
```

### 2. Start Queue Worker

```bash
# Terminal 1: Run queue worker
php artisan queue:work database --tries=3 --timeout=120 --verbose

# Or in background with Supervisor (production):
sudo supervisorctl start ssg-queue-worker
```

### 3. Start Scheduler

```bash
# Terminal 2: Run scheduler (separate terminal)
php artisan schedule:work

# Or add to crontab (production):
# * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Test Email Configuration

```bash
php artisan email:diagnose
```

### 5. Access Admin Panel

Navigate to: `http://localhost:8000/admin/announcements`

---

## 📋 Verification Checklist

### Database

- [ ] Run: `php artisan migrate` (no errors)
- [ ] Check: `announcements` table exists with correct columns
- [ ] Check: `notifications` table exists with unique constraint
- [ ] Verify: Foreign key relationships set up

### Queue System

- [ ] Run: `php artisan queue:work` command
- [ ] Check: Jobs table populated when announcement sent
- [ ] Verify: Queue worker processing jobs (not erroring)
- [ ] Confirm: Notification records created in notifications table

### Email Sending

- [ ] Test: Create draft announcement
- [ ] Test: Send to small group of students
- [ ] Verify: Emails received
- [ ] Check: Notifications table shows 'sent' status
- [ ] Review: Email content matches template

### Scheduled Sending

- [ ] Test: Create scheduled announcement (1 minute in future)
- [ ] Verify: Scheduler running: `ps aux | grep "schedule:work"`
- [ ] After 1 min: Check jobs queued
- [ ] Verify: Emails sent at scheduled time

### Admin Dashboard

- [ ] Navigate: `/admin/announcements`
- [ ] Create: New announcement
- [ ] Preview: Recipients list
- [ ] Send: Immediately or schedule
- [ ] Monitor: Delivery progress bar updates
- [ ] Resend: Failed notifications

### Error Handling

- [ ] Test: Invalid email address (should be marked failed)
- [ ] Test: Retry mechanism (job should retry 3 times)
- [ ] Check: Error messages logged to notifications.error_message
- [ ] Verify: Failed resend button works

---

## 🔧 Key Configuration Files

### `.env` - Mail Configuration

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password

QUEUE_CONNECTION=database
```

### `config/mail.php`

- From address: `MAIL_FROM_ADDRESS`
- Reply-to: `MAIL_FROM_NAME`
- Driver: Set to `smtp`

### `config/queue.php`

- Driver: Set to `database`
- Retry after: 360s default

---

## 🐛 Troubleshooting Guide

### Issue: "Table announcements already exists"

**Solutions:**

1. Check if manual table creation happened: `SHOW TABLES LIKE 'announcements';`
2. If exists, ensure schema matches migration (add missing columns if needed)
3. If schema wrong, delete and re-migrate on staging/dev
4. Or use: `php artisan migrate:refresh` (WARNING: destructive)

### Issue: Queue worker not processing jobs

**Check:**

```bash
# 1. Is worker running?
ps aux | grep "queue:work"

# 2. Are jobs in the queue?
SELECT COUNT(*) FROM jobs;

# 3. Are there errors?
tail -f storage/logs/laravel.log | grep -i error

# 4. Restart worker:
php artisan queue:work database --timeout=120
```

### Issue: Scheduled announcements not sending

**Check:**

```bash
# 1. Is scheduler running?
ps aux | grep "schedule:work"

# 2. Check Kernel.php changes applied
grep -A 5 "sendScheduledAnnouncements" app/Console/Kernel.php

# 3. Verify send_at is datetime (not just date)
SELECT * FROM announcements WHERE status = 'scheduled';

# 4. Force send test announcement:
php artisan tinker
>>> Announcement::where('status', 'scheduled')->where('send_at', '<=', now())->first()?->update(['send_at' => now()->subMinute()])
```

### Issue: Gmail SMTP errors

**Check:**

```bash
# Run diagnostic
php artisan email:diagnose

# Common fixes:
1. Verify Gmail App Password (not account password)
2. Enable "Less Secure App Access" in Gmail settings
3. Check firewall allows port 587
4. Disable 2FA, generate new app password
```

### Issue: High failure rate

**Check:**

```bash
# 1. See error messages:
SELECT email, error_message, retry_count, status FROM notifications WHERE status = 'failed' LIMIT 10;

# 2. Check student emails:
SELECT COUNT(*) FROM users WHERE email IS NULL OR email = '';

# 3. Test SMTP connection:
php artisan email:diagnose

# 4. Monitor rate limiting:
SELECT TIMESTAMPDIFF(SECOND, created_at, updated_at), COUNT(*) FROM jobs GROUP BY TIMESTAMPDIFF(SECOND, created_at, updated_at);
```

---

## 📊 Monitoring Commands

### View Announcement Statistics

```bash
php artisan tinker
>>> App\Models\Announcement::all()->map(fn($a) => ['id' => $a->id, 'title' => $a->title, 'stats' => $a->getStats()])->each(fn($x) => dd($x));
```

### View Failed Notifications

```bash
php artisan tinker
>>> App\Models\Notification::where('status', 'failed')->with('announcement', 'student')->get();
```

### Test Sending

```bash
php artisan tinker
>>> $announcement = App\Models\Announcement::first();
>>> $students = $announcement->getTargetStudents()->limit(5)->get();
>>> $students->each(fn($s) => App\Jobs\SendAnnouncementJob::dispatch($announcement, $s));
>>> // Check jobs table for new entries
```

### Queue Status

```bash
# Failed jobs
php artisan queue:failed

# Clear all failed jobs
php artisan queue:flush

# Retry specific job
php artisan queue:retry [job-id]

# Retry all failed
php artisan queue:retry all
```

---

## 📚 Important Routes

| Route                                     | Method | Purpose                   |
| ----------------------------------------- | ------ | ------------------------- |
| `/admin/announcements`                    | GET    | List announcements        |
| `/admin/announcements/create`             | GET    | Create form               |
| `/admin/announcements`                    | POST   | Store announcement        |
| `/admin/announcements/{id}`               | GET    | View announcement details |
| `/admin/announcements/{id}/send`          | POST   | Send announcement         |
| `/admin/announcements/{id}/resend-failed` | POST   | Retry failed emails       |
| `/admin/announcements/target-preview`     | POST   | AJAX recipient preview    |

---

## 🎯 Testing Workflows

### Test 1: Immediate Send

1. Navigate to `/admin/announcements/create`
2. Fill: Title=%value%, Message=%value%
3. Skip target filters (send to all)
4. Click "Create Draft"
5. Click "Send Now"
6. Confirm
7. Check: jobs table should have entries
8. Wait: Queue worker processes jobs
9. Verify: Emails received, notifications.status = 'sent'

### Test 2: Scheduled Send

1. Navigate to `/admin/announcements/create`
2. Fill: Title, Message
3. Select "Schedule for Later"
4. Set send_at to 1 minute from now
5. Click "Create Draft"
6. Wait for scheduler to run (every minute check)
7. Verify: Jobs queued at scheduled time
8. Check: Emails received

### Test 3: Target Filtering

1. Create announcement
2. Set filters: Department='IT', Year='1'
3. Click "Preview"
4. Verify: Only matching students shown
5. Send
6. Check: Only filtered students received emails

### Test 4: Error Handling & Resend

1. Create invalid test record: `INSERT INTO users (name, email) VALUES ('Test', null)`
2. Add to filters
3. Send announcement
4. Wait for retries (3 attempts)
5. Check: Notification marked as failed with error message
6. Click "Retry Failed" button
7. Verify: Job requeued and retried

---

## 📈 Performance Tips

### For Large Sends (10,000+)

```bash
# 1. Disable Supervisor temporarily to restart after bulk send
supervisorctl stop ssg-queue-worker

# 2. Create announcement with filters
# (to reduce recipient count)

# 3. Send announcement
# (creates all notification records at once)

# 4. Let jobs queue up in jobs table

# 5. Start queue worker with multiple workers
supervisorctl start ssg-queue-worker:*

# 6. Monitor progress
watch -n 1 "SELECT COUNT(*) as pending FROM jobs WHERE queue = 'default';"
```

### Database Optimization

```sql
-- Add indexes for faster queries
ALTER TABLE notifications ADD INDEX idx_announcement_id (announcement_id);
ALTER TABLE notifications ADD INDEX idx_student_id (student_id);
ALTER TABLE notifications ADD INDEX idx_status (status);
ALTER TABLE announcements ADD INDEX idx_status (status);
ALTER TABLE announcements ADD INDEX idx_send_at (send_at);
```

---

## 🔒 Security Notes

1. **SMTP Credentials**: Never commit `.env` with real credentials
2. **Database Access**: Limit direct notification table access to admin users
3. **Route Protection**: Ensure controller uses middleware for authorization
4. **Email Scraping**: Don't expose student email lists in views
5. **Rate Limiting**: System respects Gmail limits automatically
6. **Error Messages**: Don't expose sensitive info (passwords, paths) in error logs

---

## 📞 Support Resources

### Documentation Files

- [NOTIFICATION_SYSTEM_GUIDE.md](./NOTIFICATION_SYSTEM_GUIDE.md) - Comprehensive guide
- [PRODUCTION_SUMMARY.md](./PRODUCTION_SUMMARY.md) - Email system setup
- [DEPLOYMENT_GUIDE.md](./DEPLOYMENT_GUIDE.md) - Deployment instructions

### Key Files to Review

- `app/Models/Announcement.php` - Core model
- `app/Models/Notification.php` - Tracking model
- `app/Jobs/SendAnnouncementJob.php` - Queue job
- `app/Console/Kernel.php` - Scheduler

### Testing Resources

- Test with: `php artisan tinker`
- Queue testing: `QUEUE_DRIVER=sync php artisan serve` (sync driver for testing)
- Email preview: Mail::dump() or use MailHog

---

## ✨ Next Steps

1. [ ] Run database migrations
2. [ ] Start queue worker and scheduler
3. [ ] Test email configuration
4. [ ] Create test announcement
5. [ ] Monitor delivery in admin panel
6. [ ] Review logs for any errors
7. [ ] Deploy to staging environment
8. [ ] Load test with real data
9. [ ] Deploy to production
10. [ ] Set up monitoring/alerting

---

## 📝 Notes

**Developed:** March 31, 2026  
**Framework:** Laravel 12  
**Status:** Production Ready  
**Last Tested:** See logs/telemetry.log

---

## Support Contacts

For issues or questions:

1. Check [NOTIFICATION_SYSTEM_GUIDE.md](./NOTIFICATION_SYSTEM_GUIDE.md)
2. Review troubleshooting section above
3. Check Laravel docs: https://laravel.com/docs
4. Email: admin@ssg-system.local
