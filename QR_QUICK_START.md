# QR Attendance System - Quick Start Guide

## 🚀 5-Minute Setup

### 1. Database Migration ✅
Migration already ran. Verify:
```bash
cd c:\Users\gesto\OneDrive\Desktop\ssg-management-system
php artisan migrate --step
```

### 2. Queue Worker
Start the background job processor:
```bash
php artisan queue:work --queue=default
# Keep this running in a separate terminal/background
```

### 3. Test Event Creation
1. Login as Admin: http://127.0.0.1:8000/admin/events
2. Create a new event
3. Check logs: `storage/logs/laravel.log` for QR generation messages
4. Students should receive emails with QR codes (if email configured)
5. Verify: `EventQr` table has records

---

## 📱 How Students Use It

### Method 1: Scan from Email QR
1. Receive email "Attendance QR Code - {Event Title}"
2. Email contains: QR image + event details
3. Open email on phone
4. Point phone camera at QR
5. Tap notification to scan
6. Done! Attendance marked ✓

### Method 2: Portal Scanner
1. Login to student portal
2. Go to: /student/scan-attendance
3. Allow camera access when prompted
4. Point phone at QR code or printed QR
5. Scanner auto-detects and marks attendance ✓

### Method 3: Manual Entry (Fallback)
1. Go to: /student/scan-attendance
2. Scroll to "Manual Entry" section
3. Paste QR token from email link
4. Click Submit
5. Done! ✓

---

## 📊 Admin Analytics

### View Dashboard
- URL: http://127.0.0.1:8000/admin/analytics/attendance
- Shows: Overall metrics, QR stats, trends, event list

### Drill into Event Details
- Click any event in the table
- URL: http://127.0.0.1:8000/admin/analytics/attendance/{event_id}
- Shows: Attendee list, charts, department breakdown, hourly distribution

---

## 🔍 Testing Checklist

### Test 1: Event Creation & QR Generation
```
✓ Create event
✓ Check if GenerateEventQrBatch job ran
✓ Verify EventQr records in database:
  SELECT * FROM event_qrs WHERE event_id = 1;
✓ Verify all students have QRs:
  SELECT COUNT(*) FROM event_qrs WHERE event_id = 1;
```

### Test 2: Email Sending
```
✓ If email configured, student receives email
✓ Email contains embedded QR image
✓ Email has fallback clickable link
✓ Check email logs:
  SELECT * FROM email_logs WHERE email_type = 'event_qr';
```

### Test 3: QR Scanning
```
✓ Go to /student/scan-attendance
✓ Allow camera access
✓ Scan QR from email
✓ Check database:
  SELECT * FROM event_attendances WHERE event_id = 1;
✓ Verify attendance_count increased
```

### Test 4: One-Time Use
```
✓ Try scanning same QR twice
✓ Second scan should fail with "already used" message
✓ Check database:
  SELECT used_at FROM event_qrs WHERE token = '...';
  -- should have timestamp after first scan
```

### Test 5: Expiration
```
✓ Generate QR with expires_at in the past
✓ Try to scan expired QR
✓ Should get "expired" error message
```

### Test 6: Analytics
```
✓ View /admin/analytics/attendance
✓ Metrics should show correct counts
✓ Charts should display
✓ Click event link
✓ Event details should show attendees
```

---

## 🐛 Troubleshooting

### QR not generating
```bash
# Ensure queue worker is running
php artisan queue:work --verbose

# Check logs
cat storage/logs/laravel.log | grep "Generated QR"

# Manually check database
# SQL: SELECT * FROM event_qrs;
```

### Email not sending
```bash
# Test email config
php artisan tinker
// Mail::to('your-email@example.com')->send(new TestMail());

# Check mail config in .env:
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
```

### Scanner not working
```bash
# Must be HTTPS (or localhost for development)
# Check browser console for errors (F12)
# Ensure camera permissions are allowed
# Try manual entry as fallback
```

### Wrong attendance count
```bash
# Check attendance records:
// SQL: SELECT * FROM event_attendances WHERE event_id = 1;

# Check QR usage:
// SQL: SELECT COUNT(*) FROM event_qrs WHERE event_id = 1 AND used_at IS NOT NULL;

# Should match
```

---

## 📝 Database Queries

### View all QRs for an event
```sql
SELECT * FROM event_qrs 
WHERE event_id = 1
ORDER BY created_at DESC;
```

### View all attendances
```sql
SELECT 
  ea.id,
  u.name,
  e.event_title,
  ea.scanned_at
FROM event_attendances ea
JOIN users u ON ea.student_id = u.id
JOIN events e ON ea.event_id = e.id
ORDER BY ea.scanned_at DESC;
```

### Check QR stats
```sql
SELECT 
  event_id,
  COUNT(*) as total,
  SUM(CASE WHEN used_at IS NOT NULL THEN 1 ELSE 0 END) as used,
  SUM(CASE WHEN expires_at <= NOW() THEN 1 ELSE 0 END) as expired
FROM event_qrs
GROUP BY event_id;
```

### Find unused QRs for an event
```sql
SELECT * FROM event_qrs 
WHERE event_id = 1 
AND expires_at > NOW() 
AND used_at IS NULL;
```

---

## 🎯 Common Tasks

### Regenerate QRs for an event
```bash
php artisan tinker
// $event = Event::find(1);
// GenerateEventQrBatch::dispatch($event);
// Type: exit
```

### Resend emails for an event
```bash
php artisan tinker
// $event = Event::find(1);
// SendEventQrEmails::dispatch($event);
// Type: exit
```

### Remove attendance record
```bash
php artisan tinker
// EventAttendance::where('event_id', 1)->where('student_id', 123)->delete();
```

### Reset QR for a student
```bash
php artisan tinker
// $qr = EventQr::where('event_id', 1)->where('user_id', 123)->first();
// $qr->update(['used_at' => null]); // Allow rescanning
// OR delete and regenerate:
// $qr->delete();
// app(QrCodeService::class)->generateOrGetQr(Event::find(1), User::find(123), refresh: true);
```

---

## 🔒 Security Notes

1. **Tokens**: Non-predictable, using app key + UUID
2. **Expiration**: 4 hours (configurable in QrCodeService)
3. **One-time use**: Marked as used_at after scan
4. **User binding**: QR linked to specific student
5. **Database**: Foreign keys + unique constraints
6. **Validation**: Comprehensive checks before marking attendance

---

## 📊 Architecture Diagram

```
Event Created
    ↓
GenerateEventQrBatch Job
    ├─→ Loop all students
    ├─→ Create EventQr records
    └─→ Dispatch SendEventQrEmails
            ↓
    SendEventQrEmails Job
        ├─→ Get valid QRs
        ├─→ Generate QR images
        └─→ Send emails
                ↓
    Student receives email with QR
        ├─→ Option 1: Phone camera
        │       ↓
        │   Opens attendance.scan URL
        │       ↓
        │   AttendanceController
        │       ↓
        │   Validates token
        │       ↓
        │   Creates EventAttendance
        │
        └─→ Option 2: Portal Scanner (/student/scan-attendance)
                ├─→ html5-qrcode library
                ├─→ Extracts token
                └─→ Submits to attendance.scan endpoint
                        ↓
                    Same as Option 1

Analytics Dashboard
    ├─→ /admin/analytics/attendance
    │   ├─→ Overall metrics
    │   ├─→ QR statistics
    │   ├─→ Trend charts
    │   └─→ Event list
    │
    └─→ /admin/analytics/attendance/{event}
        ├─→ Event metrics
        ├─→ Attendee breakdown
        ├─→ Department distribution
        └─→ Hourly distribution
```

---

## ✨ Production Ready Features

✅ Secure tokens (non-predictable)  
✅ One-time use enforcement  
✅ Expiration handling  
✅ User binding  
✅ Batch processing via jobs  
✅ Email with embedded images  
✅ Live QR scanner  
✅ Manual fallback  
✅ Comprehensive analytics  
✅ Department breakdown  
✅ Hourly distribution  
✅ Error handling & logging  
✅ Database optimization (indices)  
✅ Backward compatibility  
✅ Mobile responsive  
✅ No N+1 queries  

---

## 📞 Support

For detailed implementation guide, see: **QR_SYSTEM_IMPLEMENTATION.md**

For code structure reference, see project documentation.
