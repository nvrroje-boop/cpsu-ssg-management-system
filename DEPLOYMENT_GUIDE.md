# SSG Management System - Production Deployment Guide

## System Status: ✅ Production Ready

This document confirms that the SSG Management System is fully prepared for real school deployment.

---

## 1. SYSTEM ENTRY POINTS

### Public Flow (No Authentication)

- **Route**: `/`
- **Endpoint**: `WelcomeController@index`
- **Display**:
    - All PUBLIC announcements
    - All PUBLIC events
    - Acts as official campus bulletin
    - Auto-updates when admin publishes

### Authentication Flow

- **Route**: `/login`
- **Method**: `LoginController`
- **Redirect Logic**:
    - Admin/SSG Officer → `/admin/dashboard`
    - Student → `/student/dashboard`

### Logout Flow

- **Route**: `POST /logout`
- **Endpoint**: `LoginController@destroy`
- **Redirect**: Always to `/` (welcome page)

---

## 2. ADMIN PORTAL (`/admin`)

### Modules

#### Dashboard

- **Route**: `/admin/dashboard`
- **Controller**: `Admin\DashboardController`
- **Features**:
    - Student count statistic
    - Active events count
    - Today's QR scans
    - Attendance rate (%)
    - Attendance by event chart (Chart.js)
    - Recent announcements
    - Recent attendance log

#### Students Management

- **Route**: `/admin/students`
- **Controller**: `Admin\StudentController`
- **Operations**:
    - Create new student
    - Edit student records
    - View student details
    - Delete student
    - Generate QR token for attendance
    - Send credentials email

#### Announcements Management

- **Route**: `/admin/announcements`
- **Controller**: `Admin\AnnouncementController`
- **Operations**:
    - Create announcement
    - Set visibility (public/private)
    - Target department
    - Edit announcement
    - Delete announcement
    - Automatic notification to audience

#### Events Management

- **Route**: `/admin/events`
- **Controller**: `Admin\EventController`
- **Operations**:
    - Create event
    - Set event date/time
    - Set location
    - Set visibility (public/private)
    - Enable/disable attendance requirement
    - Target department
    - Generate QR code
    - View attendance

#### Attendance Tracking

- **Route**: `/admin/attendance`
- **Controller**: `Admin\AttendanceController`
- **Features**:
    - View attendance summary
    - Recent attendance log
    - Attendance sessions
    - Per-student attendance rate

#### Concerns Management

- **Route**: `/admin/concerns`
- **Controller**: `Admin\ConcernController`
- **Operations**:
    - View all student concerns
    - Mark as pending/in_review/resolved
    - Assign to staff member

#### Reports & Analytics

- **Route**: `/admin/reports`
- **Controller**: `Admin\ReportController`
- **Reports**:
    - Highest attendance events
    - Most engaged departments
    - Email logs
    - System statistics

---

## 3. STUDENT PORTAL (`/student`)

### Modules

#### Dashboard

- **Route**: `/student/dashboard`
- **Controller**: `Student\DashboardController`
- **Features**:
    - Personal statistics
    - Recent announcements feed
    - Upcoming events

#### Announcements

- **Route**: `/student/announcements`
- **Controller**: `Student\AnnouncementController`
- **Features**:
    - View announcements (filtered by visibility + department)
    - Display logic:
        - PUBLIC → visible to all
        - PRIVATE → visible only if user.department_id == announcement.department_id

#### Events

- **Route**: `/student/events`
- **Controller**: `Student\EventController`
- **Features**:
    - View events (filtered by visibility + department)
    - Mark attendance (QR scan)
    - View attendance history
    - Same display logic as announcements

#### QR Attendance

- **Route**: `/student/qr-scanner`
- **Controller**: `Student\EventController@qrScanner`
- **Features**:
    - Scan QR code
    - Validate event_id + student_id
    - Prevent duplicate attendance using UNIQUE(event_id, student_id)
    - Update dashboard stats

#### Concerns Submission

- **Route**: `/student/concerns`
- **Controller**: `Student\ConcernController`
- **Operations**:
    - Submit new concern
    - View submitted concerns
    - Track status (pending/in_review/resolved)

---

## 4. DATABASE SCHEMA

### Tables Created

- `users` - Student and admin accounts
- `roles` - Admin, SSG Officer, Student
- `departments` - Academic departments
- `sections` - Course sections
- `announcements` - News/bulletins (soft delete)
- `events` - Campus events (soft delete)
- `event_attendances` - Attendance records (UNIQUE constraint)
- `email_logs` - Email history
- `system_notifications` - In-app notifications
- `concerns` - Student concerns/feedback

### Critical Constraints

```sql
-- Event attendance: prevent duplicate records
UNIQUE(event_id, student_id)

-- Foreign keys with cascading
Announcements.created_by_user_id → users.id
Events.created_by_user_id → users.id
EventAttendances.event_id → events.id
EventAttendances.student_id → users.id
Concerns.submitted_by_user_id → users.id
```

---

## 5. BUSINESS RULES IMPLEMENTED

### Visibility Rules

```
public → visible to everyone (guests + logged in users)
private → visible ONLY if:
    user.department_id == record.department_id
```

**Model Scopes**:

```php
// Both Announcement and Event models
public function scopeVisibleToUser(Builder $query, ?User $user): Builder
{
    return $query->where(function (Builder $visibilityQuery) use ($user): void {
        $visibilityQuery->where('visibility', 'public');

        if ($user?->department_id !== null) {
            $visibilityQuery->orWhere(function (Builder $privateQuery) use ($user): void {
                $privateQuery
                    ->where('visibility', 'private')
                    ->where('department_id', $user->department_id);
            });
        }
    });
}
```

### Attendance Rules

- QR scanning prevents duplicate entries via database constraint
- Each student can attend each event only once
- Unique constraint: `UNIQUE(event_id, student_id)`

### Logout Behavior

- Session destroyed
- Token regenerated
- Always redirects to `/` (welcome page)

---

## 6. API ENDPOINTS (AJAX)

### Notification Polling

- **Endpoint**: `GET /api/notifications`
- **Authentication**: Required (middleware: auth)
- **Frequency**: Every 5 seconds (JavaScript)
- **Response**:

```json
{
    "unread": 3,
    "notifications": [
        {
            "title": "New event created",
            "time": "2 minutes ago"
        }
    ]
}
```

---

## 7. UI FRAMEWORK

### Frontend Architecture

- **Bootstrap 5.3** - Responsive grid system
- **Chart.js** - Dashboard analytics
- **AdminLTE 4** - Admin dashboard template
- **FontAwesome** - Icons

### Layout Structure

```
layouts/app.blade.php
├── Navbar (top)
│   ├── Logo
│   ├── Notification bell
│   ├── User dropdown
│   └── Logout
├── Sidebar (left)
│   ├── Brand
│   └── Navigation (context-aware)
└── Main content
    ├── Breadcrumb
    ├── Flash messages
    └── Page content
```

### CSS Architecture (NO inline styles)

```
public/css/
├── welcome.css      (public landing page)
├── admin.css        (admin dashboard)
├── student.css      (student portal)
├── auth.css         (login page)
└── components.css   (shared components)
```

---

## 8. SECURITY FEATURES

✅ **Implemented**:

- Role-based access control (Admin, Student, SSG Officer)
- `AdminMiddleware` restricts to Admin/SSG Officer
- `StudentMiddleware` restricts to Student/SSG Officer
- Form request validation on all user inputs
- CSRF protection on all POST/PUT/DELETE
- Authentication middleware enforced
- Model: `findOrFail()` prevents unauthorized access
- Eager loading to prevent N+1 queries
- SQL injection prevention via Eloquent ORM
- Input sanitization via validation rules

✅ **Not Yet Implemented** (Can Add Later):

- Rate limiting
- Two-factor authentication
- IP whitelisting
- API throttling

---

## 9. ERROR HANDLING

### Validation

- All user inputs validated via Form Requests
- Graceful error messages displayed in views
- Errors collected and shown in alert boxes

### Database

- `findOrFail()` returns 404 instead of null error
- Soft deletes prevent data loss
- Foreign key constraints prevent orphaned records

### Views

- All routes have corresponding views
- No undefined variable errors
- `optional()` helper prevents null errors

---

## 10. NOTIFICATION SYSTEM

### SystemNotification Table

- Stores in-app notifications
- Linked to user
- Shows unread count in navbar
- Auto-updates via AJAX every 5 seconds

### When Notifications Trigger

1. **Announcement Created**: Notify audience
2. **Event Created**: Notify audience
3. **Attendance Marked**: Confirm to student
4. **Concern Status Updated**: Notify student

---

## 11. DEPLOYMENT CHECKLIST

✅ **Pre-Production Steps**:

```bash
# 1. Configure environment
vim .env
# Set: APP_DEBUG=false, APP_ENV=production

# 2. Cache configuration
php artisan config:cache

# 3. Cache routes
php artisan route:cache

# 4. Cache views
php artisan view:cache

# 5. Optimize auto-loading
php artisan optimize

# 6. Run migrations (if fresh DB)
php artisan migrate --force

# 7. Seed data (optional)
php artisan db:seed
```

✅ **Production Server Setup**:

```bash
# Web server (Apache/Nginx): Point root to `/public`
# Database: MySQL 8.0+ or PostgreSQL 12+
# PHP: 8.2+
# Composer: Latest
# Node.js: 18+ (for Vite assets - if using npm run build)
```

---

## 12. ARTISAN COMMANDS

### Useful Production Commands

```bash
# Start development server
php artisan serve --host=0.0.0.0 --port=8000

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Clear caches
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear

# Re-cache everything
php artisan optimize

# Create admin user
php artisan tinker
>>> User::create([...])
```

---

## 13. PERFORMANCE OPTIMIZATION

✅ **Implemented**:

- Model eager loading (with relationships)
- Route caching for production
- View caching
- Config caching
- Database indexing on foreign keys
- Query optimization with scopes

✅ **Monitoring**:

- Check Laravel logs: `storage/logs/laravel.log`
- Monitor database queries in development
- Use `php artisan tinker` for quick tests

---

## 14. SYSTEM FLOW DIAGRAM

```
┌─────────────────────────────────────────┐
│         Public (No Login)               │
│         GET /                           │
│   ├── PUBLIC announcements              │
│   └── PUBLIC events                     │
└─────────────────────────────────────────┘
                    ↓
        ┌──────────────────────┐
        │   Login Page         │
        │   POST /login        │
        └──────────────────────┘
                    ↓
        ┌──────────────────────┐
        │ Check User Role      │
        └──────────────────────┘
                ↙   ↘
        ┌──────────┐  ┌──────────────┐
        │  ADMIN   │  │   STUDENT    │
        │ Portal   │  │   Portal     │
        ├──────────┤  ├──────────────┤
        │Dashboard │  │ Dashboard    │
        │Students  │  │ Announcements│
        │Announce..│  │ Events       │
        │Events    │  │ Concerns     │
        │Attendance│  │ QR Attendance│
        │Concerns  │  └──────────────┘
        │Reports   │
        └──────────┘
                ↓
        ┌──────────────────────┐
        │   Logout             │
        │   POST /logout       │
        │   Redirect to /      │
        └──────────────────────┘
```

---

## 15. REAL-WORLD DEPLOYMENT NOTES

### Domain Setup

```
ssg.university.edu → points to /public/index.php
```

### Database Migration

```bash
# If upgrading existing system:
php artisan migrate --force
```

### Email Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.university.edu
MAIL_PORT=587
MAIL_USERNAME=noreply@university.edu
MAIL_PASSWORD=xxxxx
MAIL_FROM_ADDRESS=ssg@university.edu
MAIL_FROM_NAME="SSG System"
```

### Session Management

```env
SESSION_DRIVER=database  # Use DB for multi-server
SESSION_LIFETIME=120     # 2 hours
REMEMBER_ME_COOKIE=true
```

### Logging

```env
LOG_CHANNEL=stack
LOG_LEVEL=error  # Don't log debug in production
```

---

## 16. SUPPORT & TROUBLESHOOTING

### Common Issues

**Issue**: "ModuleNotFound" errors

- **Solution**: `php artisan optimize` clears cache

**Issue**: Login not working

- **Solution**: Check `APP_DEBUG=false` in `.env`, verify auth middleware

**Issue**: Views not updating

- **Solution**: `php artisan view:clear`

**Issue**: Routes not found

- **Solution**: `php artisan route:clear && php artisan route:cache`

**Issue**: Database connection error

- **Solution**: Verify `.env` DB credentials

---

## 17. FINAL STATUS

✅ **System Ready for Production**

- All routes configured
- All controllers implemented
- All views created
- Database schema complete
- Security measures in place
- Error handling robust
- Performance optimized
- Documentation complete

### Next Steps:

1. Deploy to production server
2. Configure domain DNS
3. Set up SSL certificate
4. Run migrations
5. Seed initial data (roles, departments)
6. Test login flow
7. Verify QR attendance
8. Monitor logs

---

**Generated**: March 28, 2026
**System**: Student Government (SSG) Management System v1.0
**Status**: ✅ Production Ready for Real School Deployment
