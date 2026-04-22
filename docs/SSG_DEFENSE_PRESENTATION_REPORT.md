# SSG Management System

## Short Defense and Presentation Report

Institution: Central Philippines State University - Hinoba-an Campus  
Project: Supreme Student Government Management System  
Framework: Laravel 12

---

## 1. Project Overview

The SSG Management System is a role-based web application designed to help the Supreme Student Government manage campus communication, events, attendance, and student concerns in one centralized platform.

The system was developed to replace manual and fragmented processes such as:

- posting announcements through separate channels
- tracking event attendance manually
- sending updates without a structured targeting system
- handling student concerns without organized records

The final system provides four access layers:

- Public portal
- Student portal
- SSG Officer portal
- Admin portal

It is database-driven, role-protected, and supported by email notifications, QR attendance, and scheduled automation.

---

## 2. Main Goal of the System

The main goal of the project is to create a working campus portal that allows the SSG to:

- publish announcements and events efficiently
- reach the correct students using targeted filters
- manage users and roles properly
- monitor attendance using secure QR codes
- respond to student concerns in an organized way
- operate through a real Laravel backend instead of demo-only pages

---

## 3. Technologies Used

The system was built using:

- Laravel 12
- Blade templating
- MySQL database
- Eloquent ORM
- Laravel queues and jobs
- Laravel scheduler
- Gmail SMTP for email notifications
- ngrok for public sharing during localhost development
- `endroid/qr-code` for QR generation

---

## 4. How the System Works

The system works through a role-based flow:

1. Users log in using their email and password.
2. Laravel checks their role.
3. The system redirects them to the correct portal.
4. Each portal only shows the features allowed for that role.
5. Data is loaded from the database through Eloquent models and services.
6. Heavy tasks such as bulk email sending are processed by queue jobs.
7. Scheduled tasks such as publishing delayed announcements are processed by Laravel's scheduler.

This structure makes the system organized, secure, and scalable.

---

## 5. Public Portal

The public portal is the front-facing side of the system. It is accessible without logging in.

Its main purpose is to:

- show the homepage
- display published announcements
- display upcoming or active events
- provide access to the login page

This allows students and visitors to view important SSG information even before authentication.

---

## 6. Admin Portal

The Admin portal is the highest-level management interface in the system.

### How the admin works

The admin is responsible for system-wide control. After logging in, the admin is redirected to the admin dashboard, where they can monitor the platform and manage major modules.

The admin can:

- manage user accounts
- create student, officer, and SSG officer records
- assign roles
- manage announcements
- manage events
- manage concerns submitted by students
- reply to concerns
- view reports and attendance analytics

### Admin account management

The admin creates accounts through the admin panel. When a new account is created:

- the data is validated
- the user is stored in the database
- the correct role is assigned
- a student number is generated when needed
- credentials can be sent by email

### Admin control of announcements and events

The admin can create announcements and events and target the correct students using filters such as:

- department
- year level
- section

This ensures that only the intended audience receives the notification.

### Admin concern management

The admin can review student concerns, update their status, and reply. The reply is stored in the database and can also be sent to the student by email.

In short, the admin portal serves as the command center of the entire system.

---

## 7. SSG Officer Portal

The SSG Officer portal is designed for operational work. It gives officers controlled access to the parts of the system they need without exposing full admin privileges.

### How the SSG officer works

After login, officers are redirected to the officer dashboard. Their role gives them access to management tools for campus communication and activities.

The SSG officer can:

- view the officer dashboard
- manage announcements
- manage events
- manage programs and members
- assist in attendance operations
- view limited reports depending on access

### Difference between admin and SSG officer

The officer can help run the SSG's day-to-day activities, but the admin still has broader control over:

- full account management
- system-wide reports
- global configuration and oversight

This separation improves security and prevents access leaks.

### Officer participation in attendance

Officers can participate in the QR attendance process by scanning or validating attendance for events. This helps distribute operational workload during campus activities.

In short, the SSG officer portal is the execution side of the system, while the admin portal is the control side.

---

## 8. Student Portal

The student portal is the user-facing portal for regular student users.

### How the student works

After login, the student is redirected to the student dashboard. The student portal is focused on receiving information and participating in campus activities.

The student can:

- view announcements
- view events
- receive event notifications
- receive event QR codes by email
- submit concerns
- view concern replies
- manage profile information

### Student concern system

One of the key features of the student portal is the concern submission module.

Instead of typing a random concern title, the student selects from a dropdown whose values come from:

- announcements
- events

This makes concern records more structured and traceable.

### Student event and attendance flow

When an event requires attendance tracking, the student receives an email containing a real QR code. During the event:

1. the student presents the QR code
2. the admin or SSG officer scans it
3. the system validates the token
4. attendance is recorded in the database

This makes attendance faster, more secure, and easier to audit.

In short, the student portal is the participation side of the system.

---

## 9. Gmail Notifications

The system uses Gmail SMTP to send emails for important actions such as:

- account credentials
- announcements
- event notices
- QR attendance emails
- concern replies

This was achieved by configuring Laravel's mail system with Gmail SMTP settings in the `.env` file and using Laravel mail classes and jobs.

For Gmail to work properly:

- valid Gmail SMTP credentials must be set
- an App Password should be used
- the queue worker must be running

Without the queue worker, email tasks remain pending and the notification system will not fully function.

---

## 10. QR Code Feature

The QR attendance feature was added to make event attendance more secure and efficient.

Each QR code is:

- generated per student
- linked to a specific event
- protected by a secure token
- limited by expiration time
- blocked from reuse after successful attendance

The QR code sent by email is an actual scannable image, so it can be read using a phone camera or QR scanner app.

This improves attendance monitoring and reduces manual encoding.

---

## 11. How Localhost Was Shared Through ngrok

During development, the project runs locally using Laravel on `localhost`. However, email recipients and mobile devices cannot open localhost links from another device.

To solve this, ngrok was used.

### How it worked

1. Laravel was started locally.
2. ngrok created a public HTTPS tunnel pointing to the local Laravel server.
3. The system detected the ngrok URL through its local API.
4. Generated links in emails and QR flows used the ngrok public URL instead of localhost.

Because of this, the system could be tested externally even while running on a local machine.

This is what allowed:

- email links to open correctly on other devices
- QR links to be scanned from phones
- external demonstration of the project during development

---

## 12. What Was Improved to Make the System Fully Work

To make the system fully functional, the following improvements were implemented:

- demo logic was replaced with real database queries
- role-based routing was completed
- admin, officer, and student portals were separated properly
- validation was tightened through form requests
- student number generation was automated
- concern submission became database-driven
- scheduled announcements were wired to the scheduler
- notifications were standardized using `read_at`
- QR tokens were secured with expiration and one-time-use logic
- email sending was moved to jobs and queues

These improvements made the project behave like a real information system rather than a static prototype.

---

## 13. Conclusion

The SSG Management System is a complete role-based Laravel application built to support the real operations of the Supreme Student Government of CPSU Hinoba-an Campus.

Its major strength is that each role has a clear responsibility:

- Admin manages the whole system
- SSG Officer manages day-to-day SSG operations
- Student receives information and participates in events and concerns

The system became fully workable because the frontend, backend, database, email notifications, QR attendance, and role authorization were connected into one complete workflow.

Overall, the project demonstrates how Laravel can be used to build a secure, scalable, and practical campus management system.
