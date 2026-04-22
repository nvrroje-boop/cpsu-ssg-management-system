# 🔧 Database Schema Mismatch Fixes - April 5, 2026

## Problem Identified
The NotificationBell component was querying a non-existent `user_id` column in the `notifications` table. The application had two separate notification systems with conflicting schemas:

### Issue Details
```
Error: Unknown column 'user_id' in 'where clause'
Location: app/Livewire/NotificationBell.php:31
Query: SELECT COUNT(*) FROM notifications WHERE user_id = 1 AND read_at IS NULL
```

### Root Causes
1. **Notification Model** (notification emails/announcements):
   - Table: `notifications`
   - Columns: `student_id`, `announcement_id`, `status`, `sent_at`, etc.
   - **NOT`user_id` - this is for email logs

2. **SystemNotification Model** (system alerts):
   - Table: `system_notifications`
   - Columns: **`user_id`** ✓, `title`, `message`, `is_read` (boolean)
   - **Model Expected**: `read_at` (datetime) but table had `is_read` (boolean)
   - **Schema Mismatch**: Model vs Database

---

## ✅ Fixes Applied

### Fix 1: SystemNotification Model
**File**: `app/Models/SystemNotification.php`

Changed:
```php
// BEFORE (WRONG)
protected $fillable = ['user_id', 'title', 'message', 'read_at'];
protected function casts(): array {
    return ['read_at' => 'datetime'];
}

// AFTER (CORRECT)
protected $fillable = ['user_id', 'title', 'message', 'is_read'];
protected function casts(): array {
    return ['is_read' => 'boolean'];
}
```

**Reason**: Database column is `is_read` (boolean), not `read_at` (datetime)

---

### Fix 2: NotificationBell Component
**File**: `app/Livewire/NotificationBell.php`

Changed:
```php
// BEFORE (WRONG)
use App\Models\Notification;

$this->unreadCount = Notification::where('user_id', $user->id)
    ->where('read_at', null)  // ❌ Column doesn't exist
    ->count();

// AFTER (CORRECT)
use App\Models\SystemNotification;

$this->unreadCount = SystemNotification::where('user_id', $user->id)
    ->where('is_read', false)  // ✓ Correct column
    ->count();
```

Updated methods:
- `loadNotifications()` - Use `SystemNotification` & `is_read`
- `markAsRead()` - Update: `['is_read' => true]`
- `markAllAsRead()` - Update: `['is_read' => true]`

---

### Fix 3: API NotificationController
**File**: `app/Http/Controllers/Api/NotificationController.php`

Changed:
```php
// BEFORE (WRONG)
use App\Models\Notification;

$notifications = Notification::query()
    ->where('user_id', $user->id)
    ->whereNull('read_at')  // ❌ Column doesn't exist
    ->count();

// AFTER (CORRECT)
use App\Models\SystemNotification;

$notifications = SystemNotification::query()
    ->where('user_id', $user->id)
    ->where('is_read', false)  // ✓ Correct column
    ->count();
```

Updated methods:
- `index()` - Query `SystemNotification`, return `is_read`
- `markRead()` - Update: `['is_read' => true]`
- `markAllRead()` - Update: `['is_read' => true]`

---

## Database Schema Reference

### Correct: system_notifications Table
```sql
CREATE TABLE system_notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL (FK -> users),
    title VARCHAR(255),
    message LONGTEXT,
    is_read BOOLEAN DEFAULT false,     ← Use this
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### For Reference: notifications Table (Announcement Emails)
```sql
CREATE TABLE notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    announcement_id BIGINT NOT NULL (FK -> announcements),
    student_id BIGINT NOT NULL (FK -> users),   ← NOT user_id
    status ENUM('queued', 'sent', 'failed', 'bounced'),
    email VARCHAR(255),
    sent_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🧪 Testing

### Test Admin Dashboard
```
1. URL: http://localhost:8000/login
2. Login with admin credentials
3. Go to: http://localhost:8000/admin
4. Dashboard should load without errors
5. Notification Bell should appear in header
```

### Test Notification API
```bash
# Get notifications (should return empty array initially)
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/notifications

# Response:
{
  "data": [],
  "unread_count": 0
}
```

### Browser Console Check
```javascript
// Should NOT show Lavewire errors
// Go to F12 → Console → No red errors
```

---

## ✨ What Was Wrong vs What's Fixed

| Aspect | ❌ Before | ✅ After |
|--------|----------|---------|
| Model | `Notification` | `SystemNotification` |
| Model Fillable | `read_at` | `is_read` |
| Model Casts | `datetime` | `boolean` |
| Query Column | `where('read_at', null)` | `where('is_read', false)` |
| Update Value | `['read_at' => now()]` | `['is_read' => true]` |
| Database Match | ❌ Mismatch | ✅ Aligned |

---

## Cache Cleared
✅ `artisan cache:clear`  
✅ `artisan view:clear`  
✅ `artisan config:clear`  

## Server Restarted
✅ Fresh instance running on http://localhost:8000

---

## Summary
The notification system now correctly queries the `system_notifications` table using the `is_read` boolean column, matching the actual database schema. No more "Unknown column" errors! 🎉
