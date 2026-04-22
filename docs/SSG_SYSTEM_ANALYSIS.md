# SSG Management System

## Full System Analysis and Operational Guide

Institution: Central Philippines State University - Hinoba-an Campus  
Project: Supreme Student Government Management System  
Framework: Laravel 12  
Application Type: Role-based web portal with public, student, officer, and admin interfaces

---

## 1. Executive Summary

This system was built as a Laravel 12 campus portal for the Supreme Student Government. It combines:

- a public-facing homepage for announcements and events
- an authenticated student portal
- an authenticated officer portal
- a full admin portal
- database-driven content management
- queued email notifications
- QR-based attendance
- scheduled announcement publishing
- role-based authorization

The project started from the Laravel application skeleton and was extended into a multi-portal campus management system. Over time, the codebase was refactored from simple Laravel scaffolding into a structured application that now uses:

- dedicated route files per portal
- Eloquent models and relationships
- service classes for reusable business logic
- form requests for strict validation
- queued jobs for heavy email work
- mail classes for notification delivery
- blade layouts and a shared design system

The result is a real database-backed system rather than a static demo interface.

---

## 2. How the System Was Created

### 2.1 Foundation

The project was created on top of the standard Laravel 12 starter application. Evidence of this is visible in `composer.json`, which uses:

- `laravel/framework`
- `laravel/tinker`
- `livewire/livewire`
- `endroid/qr-code`

Laravel provided the baseline infrastructure:

- routing
- controllers
- blade templating
- Eloquent ORM
- migrations
- seeders
- authentication/session handling
- queues
- scheduled commands
- mail delivery

### 2.2 Evolution into an SSG system

From that Laravel base, the application was expanded into a campus management platform by adding:

- separate role-aware dashboards
- public announcement and event browsing
- student concern submission
- event attendance tracking
- notification logging
- QR token generation and scan validation
- automatic email delivery for credentials, announcements, events, and QR passes
- strict student filtering by department, year, and section
- scheduled announcement publishing

### 2.3 Structure used in the project

The project follows Laravel MVC with additional service and job layers:

- `app/Http/Controllers` for request handling
- `app/Models` for Eloquent entities
- `app/Services` for business logic
- `app/Http/Requests` for validation
- `app/Jobs` for queued async work
- `app/Mail` for notification templates and delivery
- `app/Console/Commands` for background command workflows
- `resources/views` for blade templates
- `database/migrations` for schema definition
- `database/seeders` for role, department, section, and admin bootstrap data

This is the main reason the system is maintainable: business rules are not all buried inside controllers.

---

## 3. What Was Added to the System

The current system contains these major additions beyond a default Laravel install.

### 3.1 Multi-portal route architecture

The app is split into dedicated route files and loaded in `bootstrap/app.php`:

- `routes/web.php`
- `routes/auth.php`
- `routes/admin.php`
- `routes/officer.php`
- `routes/student.php`

This separation makes each portal easier to manage and reduces route confusion.

### 3.2 Role-based access control

Roles are seeded in `database/seeders/RoleSeeder.php`:

- Admin
- Officer
- Student
- SSG Officer

User role helpers live in `app/Models/User.php`, and middleware aliases are registered in `bootstrap/app.php`:

- `admin`
- `officer`
- `student`
- `role`

This ensures users only reach the pages their role is allowed to use.

### 3.3 Admin, officer, and student portals

Separate portals were added with their own routes and dashboard flows:

- Admin: full management
- Officer: operational management
- Student: consumption and participation

### 3.4 Public website

The landing page and public content views display real announcements and events from the database. The public homepage is served by `app/Http/Controllers/WelcomeController.php`.

### 3.5 Email system

The project includes a full outbound email architecture:

- `StudentCredentialsMail`
- `AnnouncementMail`
- `EventMail`
- `EventQrMail`
- `ConcernReplyMail`

Queued delivery was added so bulk notifications do not block the browser request.

### 3.6 QR attendance system

The system includes:

- per-student per-event QR token generation
- secure HMAC-based tokens
- expiration timestamps
- one-time-use enforcement
- QR image rendering
- QR email distribution
- attendance recording when scanned by admin/officer

### 3.7 Scheduler and jobs

The project uses Laravel jobs and scheduler features for:

- scheduled announcement posting
- batch QR generation
- bulk email delivery
- recurring cleanup tasks

### 3.8 Shared UI design system

A shared visual system was added through:

- `public/css/design-system.css`
- `public/css/welcome.css`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/guest.blade.php`

This gives the project a consistent green-and-gold CPSU/SSG identity across all portals.

---

## 4. What Was Changed or Refactored

The current implementation reflects a number of important improvements over a basic or earlier-stage build.

### 4.1 Demo logic was replaced with database logic

The system now relies on Eloquent and database relationships instead of placeholder arrays or static markup. Examples include:

- public home statistics from real `User`, `Announcement`, and `Event` data
- student concern title options loaded from announcements and events
- student filtering based on real departments, sections, and year mappings
- announcement recipients based on actual student query filters

### 4.2 Controllers were made role-aware

Authentication and redirects now route users to their correct portal. This is handled in `app/Http/Controllers/Auth/LoginController.php`.

### 4.3 Student account management was generalized

The admin "students" module became a broader account management surface for manageable roles:

- Student
- Officer
- SSG Officer

The logic for this is centralized in `app/Services/StudentService.php`.

### 4.4 Validation was tightened

Instead of trusting form input directly, the system now uses request classes such as:

- `StoreStudentRequest`
- `StoreAnnouncementRequest`
- `StoreEventRequest`
- `StoreConcernRequest`
- `UpdateConcernRequest`

This reduces bad data, null issues, and invalid cross-field combinations.

### 4.5 Notification storage was standardized

Unread/read tracking was standardized to use `read_at` timestamps instead of `is_read`, which aligns better with Laravel conventions and notification-style systems.

### 4.6 Scheduled content was properly wired

Scheduled announcements are no longer just a status field in the database. They are processed by:

- `app/Console/Commands/ProcessScheduledAnnouncements.php`
- registered in the scheduler inside `bootstrap/app.php`

### 4.7 QR security was hardened

Predictable attendance tokens were replaced with secure tokens generated like this:

- HMAC hashing
- UUID input
- application key as the secret

The system also checks:

- if the token is expired
- if it was already used
- if the student was already marked present

### 4.8 UI consistency was improved

The application was moved to a shared dashboard shell and design language so public pages, admin pages, officer pages, and student pages feel like parts of one system instead of separate prototypes.

---

## 5. High-Level Architecture

### 5.1 Request flow

At a high level, the system works like this:

1. The browser sends a request to a route.
2. The route points to a controller action.
3. Middleware checks authentication and role access.
4. Form requests validate incoming data.
5. Controllers call services or models.
6. Services apply business rules and database queries.
7. Jobs are dispatched for slow tasks like bulk emails.
8. Views render real database data back to the user.

### 5.2 Application layers

#### Controllers

Controllers orchestrate the request and response cycle. Example responsibilities:

- render dashboards
- store records
- update records
- dispatch jobs
- return success/error status messages

#### Services

Services contain business logic that would otherwise clutter controllers. Example services:

- `StudentService`
- `StudentNumberService`
- `StudentFilterService`
- `ConcernService`
- `AnnouncementDispatchService`
- `EventNotificationService`
- `QrCodeService`
- `AttendanceService`

#### Models

Models define relationships and query scopes. Examples:

- `User`
- `Role`
- `Announcement`
- `Event`
- `Concern`
- `EventQr`
- `EventAttendance`
- `SystemNotification`
- `EmailLog`

#### Jobs

Jobs handle asynchronous work:

- `SendAnnouncementEmails`
- `SendEventNotificationEmails`
- `GenerateEventQrBatch`
- `SendEventQrEmails`

#### Mailables

Mailables define the email content and attachments:

- `StudentCredentialsMail`
- `AnnouncementMail`
- `EventMail`
- `EventQrMail`
- `ConcernReplyMail`

---

## 6. Role-Based System Design

### 6.1 Public portal

The public portal is accessible without login and is responsible for:

- showing the homepage
- showing visible announcements
- showing visible events
- acting as the entry point to the login page

The public homepage uses real counts and visible content through `WelcomeController`.

### 6.2 Student portal

Student routes live in `routes/student.php`. Students can:

- view their dashboard
- browse announcements
- view events
- register/confirm attendance-related event participation flow
- submit concerns
- view concern replies
- manage profile data

### 6.3 Officer portal

Officer routes live in `routes/officer.php`. Officers can:

- view their dashboard
- manage announcements
- manage events
- manage programs and members
- view reports in a limited/read-only capacity
- scan or help process event attendance

### 6.4 Admin portal

Admin routes live in `routes/admin.php`. Admins can:

- manage users/accounts
- manage announcements
- manage events
- manage reports
- manage concerns and replies
- manage system-facing records
- access full analytics and oversight flows

---

## 7. Database and Core Data Model

The system is database-driven. Some of the most important tables/entities are:

### 7.1 Users and roles

- `users`
- `roles`

Each user belongs to one role and may also belong to:

- a department
- a section

### 7.2 Academic structure

- `departments`
- `sections`

Sections are tied to year and department rules, which are enforced in the application.

### 7.3 Content

- `announcements`
- `events`
- possibly related program/member records depending on current module usage

### 7.4 Communication

- `system_notifications`
- `email_logs`
- `concerns`

### 7.5 Attendance

- `event_qrs`
- `event_attendances`

The `event_qrs` table was added in `database/migrations/2024_04_05_create_event_qrs_table.php` with:

- `event_id`
- `user_id`
- `token`
- `expires_at`
- `used_at`

This table is central to secure attendance scanning.

---

## 8. How Login Works

### 8.1 Authentication method used

The system uses Laravel session-based authentication with email and password credentials. The login logic is in `app/Http/Controllers/Auth/LoginController.php`.

This means:

- users log in with their account email and password
- Laravel creates the authenticated session
- middleware protects portal routes
- the user is redirected based on role

### 8.2 Role-based redirect after login

After successful login:

- Admin goes to `admin.dashboard`
- Officer and SSG Officer go to `officer.dashboard`
- Student goes to `student.dashboard`

If a user has an unsupported role, login is rejected for portal access.

### 8.3 Important clarification about Gmail login

This project uses Gmail as an SMTP provider for sending email notifications. It does **not** currently implement Google OAuth or "Login with Gmail".

So there are two separate concepts:

- portal login: Laravel email/password authentication
- Gmail integration: SMTP mail delivery for outgoing messages

If you want Google Sign-In later, that would be a separate feature and would require Socialite or another OAuth implementation.

---

## 9. How Account Creation and Credential Delivery Work

### 9.1 Role bootstrap

When the database is seeded, `DatabaseSeeder` calls:

- `RoleSeeder`
- `DepartmentSeeder`
- `SectionSeeder`
- `AdminSeeder`

`AdminSeeder` creates the initial admin account using environment values:

- `ADMIN_EMAIL`
- `ADMIN_PASSWORD`

If those are not set, fallback defaults are used.

### 9.2 Admin-created accounts

The admin account management surface is handled through `app/Http/Controllers/Admin/StudentController.php`.

That module creates and manages:

- students
- officers
- SSG officers

### 9.3 Student number generation

If the student number is blank, the system generates one using `StudentNumberService`.

Format:

- `YYYY-XXXXX-N`

Example:

- `2026-03000-N`

The service uses transaction-safe logic and duplicate checks so numbers are not easily repeated under concurrent inserts.

### 9.4 Credentials email

When an account is created, the system can send a credentials email through `StudentCredentialsMail`.

That email typically includes:

- name
- email
- temporary password
- login link

The login link is generated through `AppUrl::route('login')`, which is important when the system is exposed through ngrok.

---

## 10. How Announcements Work

### 10.1 Announcement creation

Announcements are managed in the admin and officer portals. Validation is handled by `StoreAnnouncementRequest`.

Announcement data includes:

- title
- message/description
- status
- visibility
- recipient filters
- department/year/section targeting

### 10.2 Visibility rules

The `Announcement` model includes scopes such as:

- `published()`
- `notArchived()`
- `archived()`
- `visibleToUser()`

This means students only see announcements they are supposed to see.

### 10.3 Targeting students

Announcement targeting is database-driven through `getTargetStudents()` and `StudentFilterService`.

The filtering can use:

- department
- year
- section

This replaced overly broad queries and prevents sending notices to unrelated students.

### 10.4 Sending announcement emails

When an announcement is sent:

1. the announcement is validated and stored
2. the system computes matching students
3. a queued job dispatches the email work
4. `AnnouncementDispatchService` sends the actual emails
5. notification and email log records are updated

This keeps the UI responsive while bulk mail is processing.

---

## 11. How Events Work

### 11.1 Event creation

Events are handled in the admin/officer portal with `StoreEventRequest`.

Events include:

- title
- description
- date
- time
- location
- department visibility rules

### 11.2 Student-facing visibility

Students only see events allowed for them through the `Event` model visibility logic.

### 11.3 Event email delivery

When an event is created or pushed to recipients:

1. the event is stored in the database
2. the matching student audience is resolved
3. `SendEventNotificationEmails` is queued
4. `EventNotificationService` sends the email notifications
5. `SystemNotification` and `EmailLog` are updated

---

## 12. How the Concern System Works

### 12.1 Student concern creation

Students submit concerns from the student portal.

The important improvement here is that the concern title is no longer free-text only. It is now driven by real content sources through `ConcernService`.

### 12.2 Title/source dropdown logic

The title dropdown is built from:

- visible published announcements
- visible events

The selected value is stored as a `source_reference`, such as:

- `announcement:12`
- `event:7`

The service then resolves that source safely before storing the concern.

### 12.3 Admin concern handling

Admins can:

- list concerns
- view concern details
- update concern status
- assign concern handling
- send reply messages

Replies are stored in the concern record and can also trigger `ConcernReplyMail`.

---

## 13. How the Student Filter and Section Logic Work

This is one of the most important correctness rules in the project.

The system must not mix unrelated sections across departments or year levels. That logic is handled in:

- `StudentFilterService`
- `StoreStudentRequest`
- `StudentSectionRules`
- announcement/event targeting requests where applicable

Example:

If the selected department is BEED and the selected year is 1, the section options must only come from BEED Year 1 sections.

This protects:

- student registration consistency
- targeting accuracy
- notification accuracy
- UI dropdown correctness

---

## 14. How Scheduled Announcements Work

Scheduled announcement publishing is implemented with:

- `app/Console/Commands/ProcessScheduledAnnouncements.php`
- scheduler registration in `bootstrap/app.php`

The scheduler runs the command every minute. The command finds announcements with:

- status = scheduled
- `scheduled_at <= now()`

Then it publishes them and dispatches the announcement email pipeline.

This means scheduled announcements are not just stored as draft records; they are actively processed by Laravel's scheduler.

---

## 15. How the QR Code System Works

### 15.1 QR package

The project uses `endroid/qr-code`, which is installed in `composer.json`.

### 15.2 QR token generation

QR generation is handled in `app/Services/QrCodeService.php`.

For each student-event pair, the service creates or reuses a valid `EventQr` record.

The token is generated using:

- `Str::uuid()`
- `hash_hmac('sha256', ..., config('app.key'))`

This is much more secure than predictable numeric or plain-string tokens.

### 15.3 QR expiration

Each QR has an `expires_at` value. The service tries to align expiry with the event date/time and then adds a grace window.

If the event date is unavailable, it falls back to a shorter safe expiry.

### 15.4 One-time-use protection

The `EventQr` model contains a `valid()` scope and a `used_at` timestamp. When attendance is recorded:

- the QR token is checked
- expired tokens are rejected
- already-used tokens are rejected
- duplicate attendance for the same event/student is rejected
- `used_at` is set

### 15.5 What the QR actually contains

The QR code does not store plain student data. It encodes a scan URL pointing to the attendance scan endpoint:

- `attendance.scan`

This means a camera or QR scanner opens a secure link containing the token, and the application decides whether that token is valid.

### 15.6 How scanning works

The attendance endpoint is handled by `AttendanceController`.

Flow:

1. Admin or officer scans the QR through a camera or QR app.
2. The scan opens the secure attendance URL.
3. The app validates the token.
4. If valid, it creates an `EventAttendance` record.
5. The token is marked as used.
6. Reuse is blocked.

### 15.7 QR image delivery

The project supports multiple QR delivery methods:

- embedded image in the email
- QR view page
- QR image endpoint
- QR download endpoint
- file attachment in the email when available

This is implemented in `EventQrMail` and the attendance QR routes.

### 15.8 Why the QR can be scanned by a phone camera

The email now sends an actual QR image, not just a raw token string. Because the QR encodes a real URL, any phone camera or QR-scanner app can read it and open the attendance link.

This only works correctly for recipients outside your local machine if the generated URL is publicly reachable, which is where ngrok becomes important.

---

## 16. How Gmail Notifications Were Achieved

### 16.1 Mail transport configuration

The Laravel mail configuration is in `config/mail.php`. The SMTP mailer is set up to work with Gmail by default:

- host: `smtp.gmail.com`
- port: `587`
- encryption: `tls`

### 16.2 Required environment values

To make Gmail notifications work in a real environment, the `.env` file needs values such as:

- `MAIL_MAILER=smtp`
- `MAIL_HOST=smtp.gmail.com`
- `MAIL_PORT=587`
- `MAIL_ENCRYPTION=tls`
- `MAIL_USERNAME=your-gmail-address`
- `MAIL_PASSWORD=your-gmail-app-password`
- `MAIL_FROM_ADDRESS=your-gmail-address`
- `MAIL_FROM_NAME="SSG Management System"`

Important: for Gmail, the password should be an **App Password**, not the normal Gmail password, especially when 2-factor authentication is enabled.

### 16.3 Queue requirement for email sending

Bulk email tasks are queued. This means Gmail notifications will only fully work if the queue worker is running.

This project is already configured for:

- `QUEUE_CONNECTION=database`

So you must run a queue worker, for example:

```powershell
php artisan queue:work
```

or use the provided Composer development script:

```powershell
composer run dev
```

That script starts:

- the Laravel server
- the queue listener
- Vite

### 16.4 Types of Gmail notifications used in the system

Gmail can be used to send:

- newly created account credentials
- announcement notifications
- event notifications
- event attendance QR emails
- concern replies

### 16.5 Built-in email troubleshooting tools

The project includes email diagnostics:

- `app/Utilities/EmailDebugger.php`
- `app/Console/Commands/DiagnoseEmailCommand.php`
- `app/Console/Commands/EmailStatsCommand.php`

Useful commands:

```powershell
php artisan email:diagnose
php artisan email:diagnose --test-email=youraddress@gmail.com
php artisan email:stats
```

These help verify SMTP connectivity, credentials, and recent email activity.

---

## 17. How Localhost Was Shared Through ngrok

### 17.1 The problem ngrok solves

Your Laravel app normally runs on localhost, such as:

- `http://127.0.0.1:8000`
- `http://localhost:8000`

That address only works on your own computer. Students and external email recipients cannot open links that point to localhost.

### 17.2 The solution implemented in this project

This project includes ngrok-aware public URL generation through:

- `app/Support/NgrokUrlResolver.php`
- `app/Support/AppUrl.php`
- ngrok settings in `config/services.php`
- ngrok settings in `.env.example`

When `NGROK_AUTO_DETECT=true`, the system checks ngrok's local API:

- `http://127.0.0.1:4040/api/tunnels`

It reads the available tunnels, prefers HTTPS, and uses that public URL as the application base URL for generated links.

### 17.3 Why this matters

Because of `AppUrl`, the system can build public links for:

- login URLs in emails
- announcement links
- event links
- QR image URLs
- QR download URLs
- attendance scan URLs

So even if Laravel is running locally, the links sent by email can still point to a real public HTTPS URL when ngrok is active.

### 17.4 Typical way you ran it

A working local + ngrok setup usually looks like this:

```powershell
php artisan serve
php artisan queue:work
npm run dev
ngrok http 8000
```

or simply:

```powershell
composer run dev
ngrok http 8000
```

With ngrok running, Laravel stays local, but email recipients and mobile scanners can access the app through the ngrok tunnel.

### 17.5 Practical result

This is how you were able to share a localhost-based system outside your device:

- Laravel served the app locally
- ngrok exposed that app to the internet
- `NgrokUrlResolver` detected the public URL
- `AppUrl` generated email and QR links using that public URL

That is the key integration that made external testing and scanning possible.

---

## 18. How the System Was Made Fully Functional

The system became fully functional by fixing the whole chain, not just individual pages.

### 18.1 Routes were completed

The application now has dedicated route surfaces for:

- public
- auth
- admin
- officer
- student

This removed broken or missing role surfaces.

### 18.2 Role authorization was enforced

Middleware and user role helpers now prevent access leaks across portals.

### 18.3 Data is database-driven

Instead of placeholder content, views now rely on:

- Eloquent queries
- model relationships
- scopes
- filters

### 18.4 Validation prevents invalid state

Form requests and rule helpers reduce:

- invalid role assignments
- invalid section/year combinations
- malformed announcement targeting
- invalid concern source references

### 18.5 Long-running tasks were moved to queues

This is essential for a real system. Without queues, sending many emails from a single request would make the browser hang or time out.

### 18.6 Background automation was added

Schedulers and jobs were introduced for:

- scheduled announcements
- QR generation
- queued notifications
- cleanup operations

### 18.7 Notification tracking was standardized

Using `read_at` and email logs made the communication system measurable and consistent.

### 18.8 Tests and verification

The project has feature and unit tests covering important flows, and the application has been checked with:

- route registration
- scheduler registration
- automated tests

This helped confirm that the current implementation boots and behaves consistently.

---

## 19. Step-by-Step: How to Make the System Fully Work

To make this system work end to end on a fresh machine, these are the practical steps.

### 19.1 Install dependencies

```powershell
composer install
npm install
```

### 19.2 Create and configure `.env`

Copy `.env.example` to `.env`, then set:

- app name and URL
- database credentials
- mail credentials
- queue connection
- optional admin bootstrap credentials

### 19.3 Generate app key

```powershell
php artisan key:generate
```

### 19.4 Prepare the database

Create the MySQL database, then run:

```powershell
php artisan migrate
php artisan db:seed
```

This creates:

- schema
- roles
- departments
- sections
- initial admin account

### 19.5 Start the app

For development:

```powershell
composer run dev
```

This starts the web server, queue listener, and frontend asset watcher.

### 19.6 Make email actually work

Update the mail settings in `.env` to real Gmail SMTP values and make sure the queue worker is running.

### 19.7 Make scheduling actually work

For development, you can run:

```powershell
php artisan schedule:work
```

For deployment, register Laravel's scheduler in the OS scheduler or process manager so it runs every minute.

### 19.8 Make external links work

If you need public testing from phones or external users:

```powershell
ngrok http 8000
```

Keep ngrok running so public links resolve correctly.

### 19.9 Verify functionality

Recommended checks:

1. log in as admin
2. create a student/officer account
3. confirm credentials email is queued and sent
4. create an announcement and verify targeted delivery
5. create an event and verify event email delivery
6. verify the QR email contains a real QR image
7. scan the QR with a phone camera
8. confirm attendance is stored
9. submit a concern as a student
10. reply as admin and confirm the reply is visible and optionally emailed

---

## 20. Operational Notes for Production or Demo Sharing

To keep the system stable, the following services should always be considered part of the application:

- web server
- database server
- queue worker
- scheduler
- mail transport
- public URL provider when local testing externally

If any one of those is missing, part of the system will appear "broken" even when the code is correct.

Examples:

- no queue worker: emails and QR sends stay queued
- no scheduler: scheduled announcements never publish
- wrong mail credentials: Gmail notifications fail
- no ngrok while sending localhost links: recipients cannot open links from outside your machine

---

## 21. Key Files That Explain the System

These are the most important files for understanding the project.

### Core bootstrapping

- `bootstrap/app.php`
- `composer.json`
- `.env.example`

### Routing

- `routes/web.php`
- `routes/auth.php`
- `routes/admin.php`
- `routes/officer.php`
- `routes/student.php`

### Authentication and portal routing

- `app/Http/Controllers/Auth/LoginController.php`
- `app/Models/User.php`

### User/account management

- `app/Http/Controllers/Admin/StudentController.php`
- `app/Services/StudentService.php`
- `app/Services/StudentNumberService.php`
- `app/Http/Requests/StoreStudentRequest.php`

### Announcements and events

- `app/Http/Controllers/Admin/AnnouncementController.php`
- `app/Http/Controllers/Admin/EventController.php`
- `app/Models/Announcement.php`
- `app/Models/Event.php`
- `app/Services/AnnouncementDispatchService.php`
- `app/Services/EventNotificationService.php`

### Concerns

- `app/Http/Controllers/Student/ConcernController.php`
- `app/Http/Controllers/Admin/ConcernController.php`
- `app/Services/ConcernService.php`
- `app/Models/Concern.php`

### QR attendance

- `app/Services/QrCodeService.php`
- `app/Http/Controllers/AttendanceController.php`
- `app/Models/EventQr.php`
- `app/Models/EventAttendance.php`
- `app/Mail/EventQrMail.php`

### Public URL and ngrok support

- `app/Support/NgrokUrlResolver.php`
- `app/Support/AppUrl.php`
- `config/services.php`

### Email diagnostics

- `app/Utilities/EmailDebugger.php`
- `app/Console/Commands/DiagnoseEmailCommand.php`
- `app/Console/Commands/EmailStatsCommand.php`

### UI layer

- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/guest.blade.php`
- `resources/views/welcome.blade.php`
- `public/css/design-system.css`
- `public/css/welcome.css`

---

## 22. Final Conclusion

This SSG Management System works because it is not just a collection of pages. It is a coordinated Laravel application with:

- role-based routing
- strict authorization
- database-backed modules
- validated forms
- queued mail delivery
- scheduled automation
- QR-based attendance security
- ngrok-aware public URL generation

The most important technical decisions that made the system fully workable were:

1. separating the portals by role
2. moving business logic into services
3. validating cross-field rules with request classes
4. using database-backed queries instead of demo data
5. using queued jobs for email operations
6. using scheduled commands for timed publishing
7. using secure QR tokens with expiration and single-use protection
8. resolving public URLs through ngrok for external access during local development

In short, the system was made fully functional by connecting every visible feature to the real backend flow:

- database
- routes
- controller logic
- validation
- services
- jobs
- mail
- scheduling
- public URL resolution

That is what turned it from a prototype into a working campus portal.
