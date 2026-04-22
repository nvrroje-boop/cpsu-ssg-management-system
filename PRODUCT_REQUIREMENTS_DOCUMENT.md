# SSG Management System

Current-State Product Requirements Document

Version: 1.0
Date: April 4, 2026
Prepared from: implemented Laravel codebase analysis

## 1. Product Summary

The SSG Management System is a web-based platform for Central Philippines State University, Hinoba-an Campus, designed to support Supreme Student Government operations across two main audiences:

- Admin / SSG Officer users who manage students, announcements, events, attendance, concerns, and reports
- Student users who log in to view announcements, track events, mark attendance, manage their profile, and submit concerns

The system also includes a public landing page for institution branding and portal access.

The product is implemented as a Laravel 12 application with server-rendered Blade views, MySQL-backed data storage, session-based authentication, QR-based attendance flows, and SMTP email delivery.

## 2. Product Vision

Provide a centralized, role-based digital system that helps the SSG communicate with students, manage campus event participation, maintain attendance records, and streamline student support concerns through a single portal.

## 3. Problem Statement

The student government needs a system that reduces manual coordination in the following areas:

- Student account provisioning and access distribution
- Dissemination of announcements to specific student groups
- Publication and tracking of events
- Recording and reviewing attendance
- Collection and resolution of student concerns
- Visibility into participation and communication activity

Without a unified platform, these workflows are fragmented across manual spreadsheets, chat messages, social media posts, and in-person coordination.

## 4. Goals

- Centralize student-facing communications and event information
- Give admins a structured way to manage student records
- Provide a student portal with personalized access based on department and role
- Record attendance for events in a trackable way
- Enable concern submission and resolution workflows
- Support operational email notifications for credentials, announcements, and events
- Provide lightweight reporting and dashboard visibility for the SSG team

## 5. Non-Goals

- Online voting or election management
- Payment processing
- Full academic SIS integration
- Multi-campus tenant management
- Parent/guardian access
- Mobile native applications
- Real-time chat or messaging

## 6. Primary Users and Roles

### 6.1 Admin

Represents high-privilege users with access to the admin portal.

Responsibilities:

- Manage student records
- Create, edit, send, and monitor announcements
- Create and manage events
- Review attendance summaries
- Review and update concerns
- View reports

System role mapping:

- `Admin`
- `SSG Officer`

### 6.2 Student

Represents students with access to the student portal.

Responsibilities:

- Sign in to the portal
- View announcements visible to them
- View event details
- Mark attendance
- Scan QR codes for events
- Edit profile and change password
- Submit and track concerns

System role mapping:

- `Student`

### 6.3 Public Visitor

Represents unauthenticated users visiting the landing page.

Responsibilities:

- View branded landing page
- See public announcements and public events
- Navigate to login

## 7. Product Scope

### 7.1 Public Website

Implemented:

- Public landing page
- Branded hero section
- Public announcements feed
- Public events feed
- Portal login CTA

### 7.2 Authentication

Implemented:

- Email/password login
- Role-aware redirect after login
- Logout
- Middleware-based route protection

Not implemented:

- Forgot password
- Password reset by email
- Email verification
- MFA

### 7.3 Admin Portal

Implemented modules:

- Dashboard
- Student management
- Announcement management
- Event management
- Attendance overview
- Concern management
- Reports overview

### 7.4 Student Portal

Implemented modules:

- Dashboard
- Announcements
- Events
- QR attendance scanner
- Concerns
- Profile management

### 7.5 Notification and Email Layer

Implemented:

- Student credentials email
- Announcement email queueing
- Event email notification dispatch
- Email logs
- System notifications table
- Notification polling endpoint

Partially implemented or incomplete:

- Scheduled announcement sending
- Notification read-state handling
- Full delivery lifecycle tracking for all email types

## 8. Functional Requirements

## 8.1 Public Landing Page

The system shall:

- Display institutional branding for CPSU Hinoba-an Campus SSG
- Display public announcements
- Display public events
- Provide entry to the login page
- Present a responsive layout for desktop and mobile

## 8.2 Login and Access Control

The system shall:

- Allow users to sign in using email and password
- Redirect admin-capable users to the admin dashboard
- Redirect student users to the student dashboard
- Prevent unauthorized users from accessing protected routes
- Show a session message when unauthorized portal access is attempted

Business rules:

- Admin portal access requires `Admin` or `SSG Officer`
- Student portal access requires `Student`

## 8.3 Student Management

The admin portal shall allow authorized users to:

- View a student list
- Create student accounts
- Edit student accounts
- View student details
- Delete student records
- Resend credentials email

The system shall:

- Validate department, year, and section relationships
- Auto-generate a student number if missing
- Auto-generate a QR token if missing
- Auto-generate a temporary password if none is supplied
- Email credentials at creation time
- Generate and email a new temporary password on credential resend
- Restore the old password if credential resend email fails

Business rules:

- Student email must be unique
- Student number must be unique if present
- QR token must be unique if present
- Section must match selected year and department
- A user cannot delete their own currently signed-in account

## 8.4 Student Profile

The student portal shall allow a student to:

- View profile information
- Update name and email address
- Change password

Business rules:

- Email must remain unique
- Password change requires current password confirmation
- New password must be confirmed

## 8.5 Announcement Management

The admin portal shall allow authorized users to:

- Create announcements as draft
- Edit draft announcements
- Delete draft announcements
- Define audience filters by department, year, and section
- Choose public or private visibility
- Send announcements immediately
- Schedule announcements for later
- View per-recipient delivery details
- Requeue failed announcement notifications

The system shall:

- Store announcement status
- Store target audience filters
- Calculate matched recipients
- Track counts for total, sent, failed, and queued recipients
- Prevent editing or deleting non-draft announcements

Business rules:

- Public announcements are visible to all students
- Private announcements are visible only to matching department users
- Announcement messages require minimum content length
- Target filters may include department, year, and section

Current implementation note:

- Immediate send queues announcement emails
- Scheduled status can be stored, but an automated scheduler to process scheduled sends is not present in the codebase

## 8.6 Student Announcements

The student portal shall allow students to:

- View announcements visible to them
- Open an announcement detail page

Visibility rules:

- Public announcements are visible to all students
- Private announcements are visible only to students within the assigned department

## 8.7 Event Management

The admin portal shall allow authorized users to:

- Create events
- Edit events
- Delete events
- Set visibility as public or private
- Select department scope for private events
- Mark whether attendance is required
- View generated QR codes for events
- View attendance totals per event

The system shall:

- Notify the target audience when an event is created or updated
- Create in-app system notifications for target users
- Attempt to send event emails to recipients with an email address

Visibility rules:

- Public events are visible to all students
- Private events are visible only to students in the selected department

## 8.8 Student Events and Attendance

The student portal shall allow students to:

- View visible events
- View event detail pages
- Mark attendance directly
- Scan a QR token to record attendance
- Open a QR scanner page

The system shall:

- Prevent duplicate attendance entries for the same student and event
- Track attendance timestamp
- Show whether the student has already attended an event

Current implementation note:

- Attendance can be marked both through a direct attendance action and via QR scanning
- QR tokens are derived from event identifiers and are predictable in format

## 8.9 Concerns Management

The student portal shall allow students to:

- Create a concern
- View concerns they submitted

The admin portal shall allow admins to:

- View all concerns
- View concern detail
- Update concern status
- Assign a concern to a user

Concern statuses:

- `pending`
- `in_review`
- `resolved`

## 8.10 Dashboard and Reporting

The admin dashboard shall display:

- Student count
- Active events count
- Today’s scans count
- Attendance rate
- Recent announcements
- Upcoming events
- Recent attendance
- Top event attendance chart

The student dashboard shall display:

- Student name and email
- Personal attendance rate
- Recent visible announcements
- Upcoming visible events

The reports area shall display:

- Attendance summary availability
- Announcement inventory availability
- Email log availability
- Highest attendance event
- Most engaged department
- Emails logged this month

## 8.11 Notifications and Email

The system shall support:

- Student credentials emails
- Announcement emails
- Event notification emails
- Email delivery logs with type and status
- In-app system notifications

Email statuses in the model:

- `queued`
- `sent`
- `failed`
- `bounced`

Current implementation note:

- Credentials emails send immediately
- Announcement emails are queued and require queue processing
- Event notifications use queued Laravel notifications

## 8.12 API

Implemented API endpoint:

- `GET /api/notifications`

Intended purpose:

- Return unread notification count and recent notifications for the signed-in user

Current implementation note:

- The endpoint exists, but the read-state field usage is not aligned with the current `system_notifications` schema

## 9. Data Model

Core entities:

- `users`
- `roles`
- `departments`
- `sections`
- `announcements`
- `events`
- `event_attendances`
- `concerns`
- `email_logs`
- `notifications`
- `system_notifications`
- `jobs`
- `failed_jobs`

Key relationships:

- A user belongs to one role
- A user may belong to one department
- A user may belong to one section
- An announcement belongs to one creator and optionally one department
- An event belongs to one creator and optionally one department
- An event attendance belongs to one event and one student
- A concern belongs to one submitter and optionally one assignee
- An announcement notification belongs to one announcement and one student
- An email log may belong to one user
- A system notification belongs to one user

## 10. Business Rules

- Only authenticated users may access admin or student portals
- Only users with correct roles may access their portal
- Sections must align with selected year and department during student creation/editing
- Student email, student number, and QR token must remain unique where applicable
- Private announcements and events are department-scoped
- Attendance is unique per student per event
- Draft announcements are the only editable or deletable announcements
- Announcement send recipients must match filters
- Concern status must stay within approved values

## 11. User Flows

### 11.1 Admin Creates Student Account

1. Admin opens student creation page
2. Admin selects role, department, year, and section
3. Admin enters student identity details
4. System generates missing student number, password, and QR token if needed
5. System saves the account
6. System sends credentials email
7. Admin sees success or partial failure message

### 11.2 Admin Resends Student Credentials

1. Admin clicks resend credentials
2. System generates a new temporary password
3. System updates the student password
4. System sends a credentials email
5. If sending fails, the password is restored
6. Admin sees success or failure feedback

### 11.3 Admin Sends Announcement

1. Admin creates announcement as draft
2. Admin defines visibility and audience filters
3. Admin triggers send
4. System validates matching recipients exist
5. System updates announcement status
6. System queues emails and creates recipient notification records
7. Admin reviews delivery status

### 11.4 Admin Creates Event

1. Admin creates event with date, time, location, and visibility
2. System stores the event
3. System determines the audience
4. System creates in-app notifications
5. System attempts event email notifications
6. Event appears in student-facing views if visible

### 11.5 Student Marks Attendance

1. Student signs in
2. Student opens event or scanner flow
3. Student submits attendance
4. System validates event visibility and duplicate attendance
5. System records attendance timestamp
6. Student receives confirmation

### 11.6 Student Submits Concern

1. Student opens concern form
2. Student enters title and description
3. System saves the concern with `pending` status
4. Admin later reviews and updates the concern

## 12. Non-Functional Requirements

### 12.1 Platform

- PHP 8.2+
- Laravel 12
- MySQL database
- Blade-rendered UI
- Session authentication

### 12.2 Performance

- Public pages should render within normal web response times under expected school usage
- Bulk announcement delivery should use queue processing rather than blocking request-response cycles
- Admin dashboards should aggregate data with acceptable performance for current campus scale

### 12.3 Security

- Passwords must be stored using Laravel hashing
- Routes must be protected by authentication and role middleware
- CSRF protection must apply to state-changing forms
- Sensitive credentials should be delivered only to validated email addresses

### 12.4 Reliability

- Email sends should be logged
- Failed email attempts should be captured for diagnosis
- Duplicate event attendance should be prevented

### 12.5 Maintainability

- Business logic is organized into controllers, services, requests, models, and mailables
- Route groups are split by admin and student scope

## 13. Dependencies and Integrations

- Gmail SMTP for email sending
- Laravel queue system for announcement delivery and queued notifications
- Endroid QR Code library for event QR generation
- MySQL database
- Browser camera access for QR scanner UI

## 14. Current Gaps and Risks

These are important findings from the actual implementation and should be treated as product and engineering backlog items.

### 14.1 Scheduled announcements are not fully operational

- The system can save an announcement as `scheduled`
- No scheduler or background processor currently dispatches scheduled announcements automatically

### 14.2 Notification API read-state mismatch

- `system_notifications` stores `is_read`
- The API controller filters by `read_at`
- This likely breaks unread notification accuracy

### 14.3 Attendance integrity is weak

- Students can mark attendance directly without scanning a QR code
- QR tokens are predictable from event IDs
- Attendance should be protected with stronger token design, event windows, and server-side validation

### 14.4 Student listing scope is broader than intended

- Student management service currently loads all users, not only student-role records
- This may expose admin accounts in student management screens

### 14.5 Email and queue behavior is inconsistent by feature

- Credentials email now sends immediately
- Announcement emails still require a running queue worker
- Event notifications use queued notification delivery but do not log delivery status like announcements do

### 14.6 Reporting is lightweight

- Reports are summary-oriented, not export-oriented
- No CSV/PDF export is implemented
- No date-range filters are implemented for core reports

### 14.7 Testing coverage is minimal

- Only default example tests are present
- No feature tests cover authentication, student flows, announcement delivery, or attendance rules

### 14.8 Some operational tooling is outdated

- At least one cleanup console command references an outdated role schema approach
- Internal maintenance tooling should be reviewed before production use

## 15. Success Metrics

Recommended product KPIs:

- Percentage of student accounts successfully provisioned with email delivery
- Announcement delivery success rate
- Event attendance participation rate
- Concern resolution turnaround time
- Monthly active student portal users
- Number of events with attendance captured digitally
- Reduction in manual announcement distribution effort

## 16. Acceptance Criteria by Module

### Student Management

- Admin can create, edit, view, delete, and resend student credentials
- Validation errors are shown for invalid department/year/section combinations
- Duplicate emails and student numbers are blocked

### Announcements

- Admin can create and edit draft announcements
- Admin can target by department, year, and section
- Students only see announcements allowed for them
- Delivery records are visible per student recipient

### Events

- Admin can create and edit events
- Students only see events allowed for them
- Attendance cannot be duplicated for the same event and student

### Concerns

- Students can submit and list concerns
- Admin can review and update concern status

### Notifications

- Credentials resend sends a fresh temporary password
- Email failures are logged
- In-app notification endpoint returns current unread state correctly

## 17. Recommended Next Product Iteration

Priority 1:

- Implement scheduled announcement processing
- Fix system notification unread logic
- Harden attendance and QR security
- Restrict student management listing to correct roles
- Add core feature tests

Priority 2:

- Add forgot password and password reset
- Add delivery reconciliation for event notifications
- Add report filters and exports
- Add mark-as-read notification actions

Priority 3:

- Add audit logs for admin actions
- Add richer analytics dashboards
- Add announcement preview route in the UI if desired
- Add notification center UX improvements

## 18. Recommended Test Coverage

- Authentication and role-based redirects
- Admin student CRUD
- Credentials resend flow
- Announcement filtering by department/year/section
- Announcement send and delivery record creation
- Event visibility and attendance
- Duplicate attendance prevention
- Concern submission and admin resolution
- Notification API unread count
- Email logging behavior for success and failure

## 19. Release Readiness Assessment

Current status:

- Suitable as a functional internal campus system prototype or pilot
- Not yet fully production-ready for high-confidence operations without hardening

Primary reasons:

- Critical product features exist end-to-end
- Some important workflows are partially implemented
- Security and integrity controls for attendance need improvement
- Automated test coverage is not yet sufficient
- Background processing behavior is inconsistent across messaging features

## 20. Final Product Definition

The SSG Management System is a role-based campus engagement platform for CPSU Hinoba-an Campus that combines student account administration, audience-targeted announcements, event publishing, QR-assisted attendance tracking, student concern handling, and basic engagement reporting in a single web application.

Its strongest implemented value today is operational centralization. Its next stage should focus on delivery reliability, attendance integrity, notification correctness, and test coverage.
