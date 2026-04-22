# ⚡ Quick Reference - Email System Commands

## 🚀 Getting Started

```bash
# 1. Start queue worker (MUST RUN FOR EMAILS TO SEND)
php artisan queue:work

# 2. Test system once running
php artisan email:diagnose --test-email=your@email.com

# 3. Check stats
php artisan email:stats
```

## 📧 Send Emails (In Code)

### All Students

```php
$service = new \App\Services\BulkEmailService();
$stats = $service->sendAnnouncementBulk($announcement);
// Returns: ['success' => 500, 'failed' => 0, 'total' => 500]
```

### Specific Students

```php
$service = new \App\Services\BulkEmailService();
$stats = $service->sendAnnouncementBulk($announcement, [1, 5, 12]);
```

### Events

```php
$service = new \App\Services\BulkEmailService();
$stats = $service->sendEventBulk($event);
```

## 🔍 Debugging

| Problem              | Command                                           |
| -------------------- | ------------------------------------------------- |
| "Emails not working" | `php artisan email:diagnose`                      |
| "Test email send"    | `php artisan email:diagnose --test-email=x@y.com` |
| "View email stats"   | `php artisan email:stats`                         |
| "Check failed jobs"  | `php artisan queue:failed`                        |
| "Process stuck jobs" | `php artisan queue:retry all`                     |

## 📊 View Email History (In Tinker/Code)

```php
// All emails to student
\App\Models\EmailLog::where('user_id', 1)->get();

// Failed emails
\App\Models\EmailLog::where('status', 'failed')->get();

// This month's announcements
\App\Models\EmailLog::whereMonth('sent_at', now()->month)
    ->where('subject', 'like', '%Announcement%')
    ->count();
```

## ⚙️ Configuration

**In .env:**

- `MAIL_MAILER=smtp` ← Must be SMTP
- `MAIL_PASSWORD=xxx` ← Use 16-char App Password (NOT regular password)
- `QUEUE_CONNECTION=database` ← For persistence

**Start queue worker:**

```bash
php artisan queue:work
# Keep this running!
```

## 🆘 Emergency

| Issue                 | Fix                                                                  |
| --------------------- | -------------------------------------------------------------------- |
| Worker not running    | `php artisan queue:work --verbose`                                   |
| Emails stuck in queue | `php artisan queue:flush` then `php artisan queue:work`              |
| Gmail blocked access  | Create new App Password at https://myaccount.google.com/apppasswords |
| Still not working     | Run `php artisan email:diagnose` for full analysis                   |

## 📝 Templates

- **Announcement**: `resources/views/emails/announcement.blade.php`
- **Event**: `resources/views/emails/event.blade.php`
- **Layout**: `resources/views/emails/layout.blade.php`

## 📍 Key Files

```
app/Services/BulkEmailService.php          ← Main service
app/Mail/AnnouncementMail.php              ← Announcement mailable
app/Mail/EventMail.php                     ← Event mailable
app/Utilities/EmailDebugger.php            ← Debugging tool
app/Console/Commands/DiagnoseEmailCommand.php
app/Console/Commands/EmailStatsCommand.php
resources/views/emails/layout.blade.php    ← Email template
```

## ✨ Pro Tips

1. Always check queue worker is running: `ps aux | grep queue:work`
2. Emails send async, so immediate check won't show them
3. Check email in spam/promotions folder
4. Use `php artisan queue:work --once` to debug issues
5. Monitor `storage/logs/laravel.log` for errors

---

**Full documentation:** See `EMAIL_SYSTEM_GUIDE.md`
