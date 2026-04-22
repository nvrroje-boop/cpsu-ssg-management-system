# 🚀 PRODUCTION DEPLOYMENT RUNBOOK

**Quick Reference for System Administrators**  
**Framework:** Laravel 12 | **Date:** April 1, 2026

---

## ⚡ QUICK START (5 Minutes)

### After Deployment

```bash
# 1. Navigate to project
cd c:\Users\gesto\OneDrive\Desktop\ssg-management-system

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Set environment
cp .env.example .env
# EDIT .env with production values:
#   DB_USERNAME, DB_PASSWORD, MAIL_USERNAME, MAIL_PASSWORD

# 4. Generate key
php artisan key:generate

# 5. Run migrations
php artisan migrate --force

# 6. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Start queue (Terminal 1)
php artisan queue:work database --tries=3 --timeout=120

# 8. Start scheduler (Terminal 2)
php artisan schedule:work

# 9. Start server (Terminal 3)
php artisan serve
```

**Expected Output:**

```
INFO Running migrations.
INFO Configuration cached successfully.
INFO Routes cached successfully.
[success] Application ready! Local development server running.
```

---

## ✅ VERIFICATION COMMANDS

Run these to verify system is working:

### 1. Check Database Migrations

```bash
php artisan migrate:status
# EXPECTED: All migrations show [Ran]
```

### 2. Check Routes

```bash
php artisan route:list | grep announcement
# EXPECTED: 10 announcement routes listed
```

### 3. Test SMTP Connection

```bash
php artisan email:diagnose
# EXPECTED: "SMTP connection successful"
```

### 4. Test Queue Processing

```bash
php artisan queue:work --dry-run
# EXPECTED: "No jobs available"
```

### 5. Cloud Configuration

```bash
php artisan config:cache
php artisan route:cache
# EXPECTED: Both complete successfully
```

### 6. Check Application Health

```bash
php artisan tinker
> App\Models\User::count()
# EXPECTED: Returns number (1 if clean DB)
> exit
```

---

## 🧹 DATABASE CLEANUP

### Option 1: Automated Cleanup (Recommended)

```bash
php artisan system:production-cleanup
# Prompts before deleting demo data
# Removes all non-admin users
# Clears announcements
# Clears notifications
```

### Option 2: Full Fresh Start

```bash
# ⚠️ DESTRUCTIVE - Deletes ALL data
php artisan migrate:refresh --seed

# This will:
# - Drop all tables
# - Recreate them
# - Run seeders
```

### Option 3: Manual SQL Commands

```bash
# Connect to MySQL
mysql -u root ssg_management_system

# Then run:
DELETE FROM notifications;
DELETE FROM announcements;
DELETE FROM users WHERE role != 'admin';
```

---

## 🔐 SECURITY HARDENING

### 1. Set APP_DEBUG=false

```bash
# Edit .env
APP_DEBUG=false  # ← Change from true to false
```

### 2. Set Proper .env Permission

```bash
# Linux/Mac
chmod 600 .env

# Windows (PowerShell)
icacls .env /inheritance:r /grant:r "$env:USERNAME`:(F)"
```

### 3. Hide Sensitive Files

Create `.env.example` WITHOUT credentials:

```env
APP_NAME=ssg-management-system
APP_ENV=production
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
# etc... without passwords
```

### 4. Setup Firewall Rules

```bash
# Restrict admin panel IP (example)
# Allow: 192.168.1.0/24 (internal network)
# Block: 0.0.0.0/0 (internet)
```

---

## 📊 MONITORING & TROUBLESHOOTING

### Check System Status

```bash
# View real-time logs
tail -f storage/logs/laravel.log

# Check queue status
php artisan queue:failed

# List all failed jobs
php artisan queue:failed
id | email
----|--------
1  | test@example.com

# Retry failed job
php artisan queue:retry 1

# Flush all failed jobs
php artisan queue:flush
```

### Common Issues & Fixes

| Issue                         | Command                                             | Fix                           |
| ----------------------------- | --------------------------------------------------- | ----------------------------- |
| "Database connection refused" | `php artisan tinker` → `DB::connection()->getPDO()` | Check DB credentials in .env  |
| "SMTP authentication failed"  | `php artisan email:diagnose`                        | Verify Gmail App Password     |
| "Queue jobs not processing"   | `ps aux \| grep queue:work`                         | Start queue worker            |
| "Scheduler not running"       | `ps aux \| grep schedule:work`                      | Start scheduler               |
| "Routes not updating"         | `php artisan route:cache --force`                   | Clear and rebuild route cache |

---

## 📈 PRODUCTION CONFIGURATION

### For Higher Performance

#### 1. Use Redis Cache (instead of file)

```env
# .env
CACHE_STORE=redis
QUEUE_CONNECTION=redis  # Instead of database
```

**Install Redis:**

```bash
# Ubuntu
sudo apt-get install redis-server
sudo systemctl start redis-server

# Docker
docker run -d -p 6379:6379 redis:latest
```

#### 2. Use PostgreSQL (instead of MySQL) [Optional]

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ssg_db
```

#### 3. Setup Supervisor for Queue

Create `/etc/supervisor/conf.d/ssg-queue.conf`:

```ini
[program:ssg-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work database --sleep=3 --tries=3 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
stopwaitsecs=120
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
environment=LARAVEL_ENV=production
```

**Manage Supervisor:**

```bash
# Reload config
sudo supervisorctl reread

# Update
sudo supervisorctl update

# Start workers
sudo supervisorctl start ssg-queue-worker:*

# Monitor
sudo supervisorctl tail -f ssg-queue-worker:0
```

#### 4. Setup Nginx (Production Web Server)

Create `/etc/nginx/sites-available/ssg`:

```nginx
server {
    listen 80;
    listen [::]:80;

    server_name ssg.yourdomain.com;
    root /path/to/public;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    index index.html index.htm index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Enable:**

```bash
sudo ln -s /etc/nginx/sites-available/ssg /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 5. Setup SSL/HTTPS (Let's Encrypt)

```bash
sudo apt-get install certbot python3-certbot-nginx
sudo certbot certonly --nginx -d ssg.yourdomain.com
sudo certbot renew --dry-run  # Test auto-renewal
```

---

## 🚨 EMERGENCY PROCEDURES

### System Down - Restart Everything

```bash
# 1. Kill all services
pkill -f "php artisan"

# 2. Clear caches
php artisan cache:clear
php artisan config:clear

# 3. Restart fresh
php artisan config:cache
php artisan route:cache

# 4. Start services
php artisan queue:work database &
php artisan schedule:work &
php artisan serve
```

### Database Corruption - Recovery

```bash
# 1. Backup current DB
mysqldump -u root ssg_management_system > backup_$(date +%s).sql

# 2. Check for errors
php artisan tinker
> check_foreign_keys()

# 3. Fix foreign key issues
PRAGMA foreign_keys = OFF;
# ... fix orphan records ...
PRAGMA foreign_keys = ON;

# 4. Verify
php artisan migrate:status
```

### Queue Stuck - Reset

```bash
# 1. View failed jobs
php artisan queue:failed

# 2. Retry all
php artisan queue:retry all

# 3. If still problematic, flush
php artisan queue:flush

# 4. Manual cleanup
DELETE FROM jobs WHERE queue = 'default';
DELETE FROM failed_jobs;
```

---

## 📋 DEPLOYMENT CHECKLIST

Before going live:

```
[ ] Database credentials configured in .env
[ ] Gmail SMTP credentials verified
[ ] APP_DEBUG=false
[ ] APP_KEY generated
[ ] Migrations run: php artisan migrate
[ ] Cache configured: php artisan config:cache
[ ] Routes cached: php artisan route:cache
[ ] Queue worker running (supervisor)
[ ] Schedule running on crontab
[ ] HTTPS/SSL configured
[ ] Firewall rules set
[ ] Admin user created/verified
[ ] Backup scheduled
[ ] Logs monitored
[ ] Test email sent successfully
[ ] Test announcement created
[ ] Test announcement scheduled
[ ] System tested with load
[ ] Rollback plan documented
```

---

## 🔄 MAINTENANCE SCHEDULE

| Task                | Frequency | Command                            |
| ------------------- | --------- | ---------------------------------- |
| Check logs          | Daily     | `tail -f storage/logs/laravel.log` |
| Backup DB           | Daily     | `mysqldump > backup.sql`           |
| Clear old logs      | Weekly    | `php artisan log:clear`            |
| Update dependencies | Monthly   | `composer update`                  |
| Security audit      | Monthly   | Check .env access, firewall rules  |
| Performance review  | Monthly   | Check query logs, slow queries     |
| SSL renewal check   | Monthly   | `certbot renew --dry-run`          |

---

## 📞 QUICK REFERENCE

| Component | Status Check                 | Start Command               | Logs                       |
| --------- | ---------------------------- | --------------------------- | -------------------------- |
| Queue     | `ps aux \| grep queue`       | `php artisan queue:work`    | `storage/logs/laravel.log` |
| Scheduler | `ps aux \| grep schedule`    | `php artisan schedule:work` | `storage/logs/laravel.log` |
| Server    | `curl localhost:8000`        | `php artisan serve`         | `storage/logs/laravel.log` |
| SMTP      | `php artisan email:diagnose` | (auto-tested)               | `storage/logs/laravel.log` |
| Database  | `php artisan migrate:status` | (always running)            | MySQL error logs           |

---

## 📝 PRODUCTION .ENV TEMPLATE

```env
APP_NAME="SSG Management System"
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE  # Generate with: php artisan key:generate
APP_DEBUG=false
APP_URL=https://ssg.yourdomain.com

# Database - PRODUCTION VALUES
DB_CONNECTION=mysql
DB_HOST=prod-database-server
DB_PORT=3306
DB_DATABASE=ssg_production
DB_USERNAME=ssg_app_user
DB_PASSWORD=STRONG_PASSWORD_HERE

# Mail - PRODUCTION SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-ssg-email@gmail.com
MAIL_PASSWORD=your-app-password-here
MAIL_FROM_ADDRESS=notifications@ssg.yourdomain.com
MAIL_FROM_NAME="SSG Management System"

# Queue - PRODUCTION
QUEUE_CONNECTION=redis  # Or database

# Cache - PRODUCTION
CACHE_STORE=redis  # Or file

# Session
SESSION_DRIVER=cookie  # Or redis
SESSION_LIFETIME=120

# Additional Security
STRIPE_PUBLIC_KEY=
STRIPE_SECRET_KEY=
```

---

## 🎯 SUCCESS CRITERIA

Your system is production-ready when:

✅ **All migrations pass** without errors  
✅ **Queue processes jobs** without hanging  
✅ **Scheduler runs** at correct intervals  
✅ **SMTP sends emails** successfully  
✅ **Admin panel loads** under 2 seconds  
✅ **No PHP errors** in logs  
✅ **Backup runs daily** with verified restore  
✅ **Uptime monitor shows** 99.9%+  
✅ **Users report no issues** for 7 days  
✅ **Security scan shows** no vulnerabilities

---

**Last Updated:** April 1, 2026  
**Status:** ✅ Production Ready  
**Next Review:** April 15, 2026
