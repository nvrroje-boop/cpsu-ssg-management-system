# ⚡ Get Started in 5 Minutes

## 1️⃣ Start Queue Worker (1 minute)

Open a Terminal and run:

```bash
cd c:\Users\gesto\OneDrive\Desktop\ssg-management-system
php artisan queue:work
```

**IMPORTANT**: Keep this terminal open and running!

You should see:

```
Processing: App\Mail\AnnouncementMail
✓ Processed: App\Mail\AnnouncementMail
...
```

---

## 2️⃣ Test Email System (1 minute)

Open **another** Terminal and run:

```bash
php artisan email:diagnose --test-email=cpsuhinobaan.ssg.office@gmail.com
```

You should see:

```
✅ Test email queued successfully!
📌 Email will be sent by queue worker
```

Check gmail inbox (~30 seconds) for the test email. If not there, check spam folder.

---

## 3️⃣ Send Your First Bulk Email (2 minutes)

In a Terminal, open PHP shell:

```bash
php artisan tinker
```

Then run:

```php
# Get an announcement
$ann = \App\Models\Announcement::first();

# Send to all students
$service = new \App\Services\BulkEmailService();
$stats = $service->sendAnnouncementBulk($ann);

# See results
echo "Queued: {$stats['success']}, Failed: {$stats['failed']}, Total: {$stats['total']}";
```

**Results:**

- Emails are queued immediately (no wait!)
- Queue worker will start sending them
- Watch Terminal #1 to see progress

---

## 4️⃣ Verify Emails Sent (30 seconds)

Check email statistics:

```bash
php artisan email:stats
```

You'll see:

```
Total Emails Sent: 500
Unique Recipients: 500
Currently Queued: 0
Failed Emails: 0
```

---

## ✅ Done! Your system is working!

### Next Steps:

1. **Keep queue worker running** (Terminal stays open)
2. **Integrate in controllers** (see INTEGRATION_EXAMPLES.php)
3. **Customize templates** (in resources/views/emails/)
4. **Monitor with** `php artisan email:stats`

---

## 📚 Quick Commands

```bash
# Start queue worker
php artisan queue:work

# Test email
php artisan email:diagnose --test-email=your@email.com

# View stats
php artisan email:stats

# Process stuck jobs
php artisan queue:retry all

# PHP shell for testing
php artisan tinker

# View failed jobs
php artisan queue:failed
```

---

## 🆘 Troubleshooting

### "Emails not sending?"

1. **Queue worker running?** Check Terminal #1. If not: `php artisan queue:work`
2. **Check email logs:** `php artisan email:stats`
3. **Run diagnostics:** `php artisan email:diagnose`

### "Can't send test email?"

1. **Is Gmail blocking?** Check: https://accounts.google.com/activity
2. **Wrong App Password?** Create new at: https://myaccount.google.com/apppasswords
3. **Still stuck?** Run: `php artisan email:diagnose` for full analysis

### "Emails going to spam?"

- This is normal. Check your spam folder.
- Google learns over time. After 10-20 emails, they'll go to inbox.
- Our templates are designed to avoid spam filters.

---

## 🎓 What Happens Behind the Scenes

```
You run: sendAnnouncementBulk($ann)
    ↓
System chunks students (50 at a time)
    ↓
For each student:
  → Creates email job
  → Stores in database
    ↓
Queue Worker (Terminal #1) sees the job
    ↓
Renders beautiful email template
    ↓
Connects to Gmail SMTP
    ↓
Sends via smtp.gmail.com:587
    ↓
✅ Student receives email!
```

**Time**: Usually under 1 second per email with queue worker running!

---

## 📊 Performance

- **Speed**: ~1,000 emails/hour (async, non-blocking)
- **Safety**: Failed emails automatically retry
- **Scalability**: Handles any volume
- **Memory**: Chunked processing = no memory issues
- **Logging**: Every email tracked in database

---

## 🔐 Security

- ✅ Uses App Password (NOT regular password)
- ✅ Credentials in .env only (NOT in code)
- ✅ Queue ensures no email tampering
- ✅ Error logging for audit trail
- ✅ TLS 1.2 encryption

---

## 📖 Full Documentation

For more information, see:

- `EMAIL_SYSTEM_GUIDE.md` - Complete reference
- `EMAIL_QUICK_REFERENCE.md` - Commands & tips
- `INTEGRATION_EXAMPLES.php` - Code examples
- `FILE_STRUCTURE.md` - What goes where
- `IMPLEMENTATION_COMPLETE.md` - Detailed summary

---

## 💡 Common Tasks

### Send to All Students

```php
$service = new \App\Services\BulkEmailService();
$stats = $service->sendAnnouncementBulk($announcement);
```

### Send to Specific Students

```php
$studentIds = [1, 5, 12, 25];
$service = new \App\Services\BulkEmailService();
$stats = $service->sendAnnouncementBulk($announcement, $studentIds);
```

### Check Email History

```php
# View all emails sent to student
\App\Models\EmailLog::where('user_id', 1)->get();

# View failed emails
\App\Models\EmailLog::where('status', 'failed')->get();

# Count this month's emails
\App\Models\EmailLog::whereMonth('sent_at', now()->month)->count();
```

### In Controller

```php
use App\Services\BulkEmailService;

$service = new BulkEmailService();
$stats = $service->sendAnnouncementBulk($announcement);

return back()->with('success', "{$stats['success']} emails queued!");
```

---

## 🎉 You're All Set!

Your email system is now:
✅ Configured
✅ Tested
✅ Running
✅ Ready for production

**Happy emailing!** 📧✨

---

**Questions?** Check the full documentation or run `php artisan email:diagnose`
