# SSG System - Quick Reference Guide

## Common Routes

### Public Access

```
GET / → Welcome page (public announcements + events)
```

### Authentication

```
GET /login → Login form
POST /login → Process login
POST /logout → Logout & redirect to /
```

### Admin Routes (`/admin`)

```
GET /admin/dashboard → Dashboard with analytics
GET /admin/students → List students
POST /admin/students → Create student
GET /admin/announcements → List announcements
POST /admin/announcements → Create announcement
PUT /admin/announcements/{id} → Update announcement
DELETE /admin/announcements/{id} → Delete announcement
GET /admin/events → List events
POST /admin/events → Create event
GET /admin/attendance → Attendance tracking
GET /admin/concerns → View all concerns
GET /admin/concerns/{id} → Concern details
PUT /admin/concerns/{id} → Update concern status
GET /admin/reports → View reports
```

### Student Routes (`/student`)

```
GET /student/dashboard → Personal dashboard
GET /student/announcements → List announcements (filtered)
GET /student/announcements/{id} → View announcement
GET /student/events → List events (filtered)
GET /student/events/{id} → Event details
POST /student/events/{id}/attend → Mark attendance
GET /student/qr-scanner → QR scanner page
GET /student/qr-scan/{token} → Process QR scan
GET /student/concerns → My concerns
GET /student/concerns/create → Create concern form
POST /student/concerns → Submit concern
```

### API Routes (`/api`)

```
GET /api/notifications → Get unread notifications (via AJAX)
```

---

## Model Relationships

### User Model

```php
User::with('role', 'department', 'section')
    ->where('id', $id)
    ->first();
```

### Announcements (with visibility filtering)

```php
Announcement::visibleToUser($user)
    ->orderByDesc('created_at')
    ->get();
```

### Events (with visibility filtering)

```php
Event::visibleToUser($user)
    ->with('attendances')
    ->orderBy('event_date')
    ->get();
```

### Concerns

```php
Concern::with('submitter', 'assignee')
    ->where('status', 'pending')
    ->get();
```

---

## Scopes (Commonly Used)

```php
// Get public records (used in WelcomeController)
Announcement::where('visibility', 'public')->get();
Event::where('visibility', 'public')->get();

// Get filtered records (used in Student portal)
Announcement::visibleToUser($user)->get();
Event::visibleToUser($user)->get();

// Get by department
Announcement::where('department_id', $dept_id)->get();
Event::where('department_id', $dept_id)->get();
```

---

## Database Queries

### Count Active Events

```php
Event::whereDate('event_date', '>=', today())->count();
```

### Count Today's Attendance

```php
EventAttendance::whereDate('created_at', today())->count();
```

### Get Attendance Rate

```php
$total = EventAttendance::count();
$expected = Event::sum('expected_attendance');
$rate = ($total / $expected) * 100;
```

### Check Duplicate Attendance

```php
EventAttendance::where('event_id', $event_id)
    ->where('student_id', $user_id)
    ->exists();
```

### Get Recent Notifications

```php
SystemNotification::where('user_id', auth()->id())
    ->where('read_at', null)
    ->orderByDesc('created_at')
    ->limit(5)
    ->get();
```

---

## Controllers Quick Reference

### Admin\DashboardController

- `index()` → Fetch stats, announcements, events, attendance

### Admin\StudentController

- `index()` → List students
- `create()` → Student form
- `store()` → Save student
- `edit()` → Edit form
- `update()` → Update student
- `destroy()` → Delete student

### Admin\AnnouncementController

- `index()` → List all announcements
- `store()` → Create & notify audience
- `update()` → Update announcement
- `destroy()` → Soft delete

### Admin\EventController

- `index()` → List events
- `store()` → Create event with attendance tracking
- `update()` → Update event
- `destroy()` → Soft delete

### Admin\ConcernController

- `index()` → List all concerns
- `show()` → View concern details
- `update()` → Update status (pending/in_review/resolved)

### Student\AnnouncementController

- `index()` → List visible announcements (filtered)
- `show()` → View announcement

### Student\EventController

- `index()` → List visible events (filtered)
- `show()` → View event
- `attend()` → Mark attendance
- `scanQr()` → Process QR token

### Student\ConcernController

- `index()` → My concerns
- `create()` → New concern form
- `store()` → Submit concern

### Api\NotificationController

- `index()` → Get unread notifications (JSON)

---

## Blade Helper Usage

### Check Authorization

```blade
@if (auth()->user()->isAdminPortalUser())
    <!-- Admin content -->
@endif

@if (auth()->user()->isStudentPortalUser())
    <!-- Student content -->
@endif
```

### Display Visibility Badge

```blade
<span class="badge badge-{{ $record->visibility === 'public' ? 'success' : 'warning' }}">
    {{ ucfirst($record->visibility) }}
</span>
```

### Display Concern Status Badge

```blade
<span class="badge badge-{{ $concern->status === 'pending' ? 'warning' : ($concern->status === 'in_review' ? 'info' : 'success') }}">
    {{ ucfirst(str_replace('_', ' ', $concern->status)) }}
</span>
```

### Optional Date Formatting

```blade
{{ optional($record->created_at)?->format('M d, Y') ?? 'Not dated' }}
```

---

## Validation Rules

### Form Validation Examples

```php
// Student
$request->validate([
    'email' => 'required|email|unique:users',
    'name' => 'required|string|max:255',
]);

// Announcement
$request->validate([
    'title' => 'required|string|max:255',
    'description' => 'required|string',
    'visibility' => 'required|in:public,private',
    'department_id' => 'nullable|exists:departments,id',
]);

// Event
$request->validate([
    'event_title' => 'required|string',
    'event_date' => 'required|date',
    'event_time' => 'required|date_format:H:i',
    'location' => 'required|string',
    'visibility' => 'required|in:public,private',
    'attendance_required' => 'boolean',
]);

// Concern
$request->validate([
    'title' => 'required|string|max:255',
    'description' => 'required|string',
    'status' => 'required|in:pending,in_review,resolved',
]);
```

---

## Cache Operations

### Clear All Caches

```bash
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear
```

### Re-cache for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## Database Maintenance

### Fresh Migration

```bash
php artisan migrate:fresh --seed
```

### Rollback Last Migration

```bash
php artisan migrate:rollback
```

### Create New Migration

```bash
php artisan make:migration create_table_name
```

### Seed Database

```bash
php artisan db:seed
```

---

## Testing Routes

### Check All Routes

```bash
php artisan route:list
```

### Check Specific Route

```bash
php artisan route:list --name=admin.dashboard
```

### Test Authentication

```bash
php artisan tinker
>>> Auth::attempt(['email' => 'user@example.com', 'password' => 'password'])
```

---

## Environment Configuration

### .env Key Settings

```env
# App
APP_NAME="SSG Management System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ssg.university.edu

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ssg_management_system
DB_USERNAME=admin
DB_PASSWORD=xxxxx

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.university.edu
MAIL_PORT=587
MAIL_USERNAME=noreply@university.edu
MAIL_PASSWORD=xxxxx
MAIL_FROM_ADDRESS=ssg@university.edu

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

---

## Common Troubleshooting

### 404 Route Not Found

```bash
php artisan route:clear
php artisan route:cache
```

### View Not Found

```bash
php artisan view:clear
php artisan view:cache
```

### Database Connection Error

- Check `.env` credentials
- Verify MySQL is running
- Test: `php artisan tinker` → `DB::connection()->getPdo()`

### Authentication Not Working

- Check `APP_DEBUG=false` not causing issues
- Verify `auth.php` config
- Check middleware in routes

### Email Not Sending

- Verify `MAIL_*` config in `.env`
- Check mail logs: `storage/logs/laravel.log`
- Test: `Mail::raw('test', function($m) { $m->to('user@example.com'); })`

---

## Files Structure Reference

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   ├── Student/
│   │   ├── Api/
│   │   └── Auth/
│   ├── Middleware/
│   └── Requests/
├── Models/
│   ├── Announcement.php
│   ├── Event.php
│   ├── EventAttendance.php
│   ├── Concern.php
│   └── ...
└── Services/

routes/
├── web.php
├── auth.php
├── admin.php
├── student.php
└── api.php

resources/views/
├── layouts/app.blade.php
├── welcome.blade.php
├── admin/
│   ├── dashboard/
│   ├── announcements/
│   ├── events/
│   ├── concerns/
│   └── ...
└── student/
    ├── dashboard/
    ├── announcements/
    ├── events/
    ├── concerns/
    └── ...

public/css/
├── welcome.css
├── admin.css
├── student.css
├── auth.css
└── components.css

database/migrations/
├── 0001_01_01_000000_create_users_table.php
├── 2026_03_26_000200_create_announcements_table.php
├── 2026_03_26_000300_create_events_table.php
├── 2026_03_26_000400_create_event_attendances_table.php
└── 2026_03_28_123822_create_concerns_table.php
```

---

## Quick Start for New Developers

1. **Setup**: `composer install` → `npm install` → `php artisan migrate`
2. **Run**: `php artisan serve`
3. **Login**: Use seeded credentials (check `AdminSeeder.php`)
4. **Explore**: Check routes via `php artisan route:list`
5. **Debug**: Use `php artisan tinker` for quick tests

---

Last Updated: March 28, 2026
System Version: 1.0 - Production Ready
