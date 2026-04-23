# Render Deployment

This repo is ready for Render with:

- `Dockerfile` for the Laravel web app
- `render.yaml` for web, worker, cron, and Postgres
- `scripts/render-start.sh` for container startup

## Recommended Render setup

Use these services in the same region:

- Web service
- Background worker
- Cron job
- PostgreSQL database

This app uses:

- queued jobs for mail and background work
- Laravel scheduler for announcements and attendance alerts
- database-backed cache and sessions

## Before you deploy

1. Push this repo to GitHub.
2. In Render, choose `New > Blueprint`.
3. Select this repository.
4. Review the services from `render.yaml`.

## Required environment values

Render will prompt for these because they are marked `sync: false`:

- `APP_URL`
- `MAIL_MAILER`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_ENCRYPTION`
- `MAIL_FROM_ADDRESS`

Set `APP_URL` to your final public Render URL or custom domain.

Example:

```text
APP_URL=https://ssg-management-web.onrender.com
```

## Important cost note

Render Free instances are fine for testing, but not for this production-style setup.

Why:

- free web services sleep after idle time
- free web services cannot send SMTP traffic on ports `25`, `465`, or `587`
- free Postgres expires after 30 days
- workers and cron jobs are not part of Render's free web-service workflow

If you want email notifications, scheduled reminders, and queue processing to work reliably, use paid instances for at least:

- web
- worker
- cron

## After first deploy

Run this checklist in Render:

1. Open the web service and confirm `/up` is healthy.
2. Confirm the database migrations completed in the deploy logs.
3. Set the mail credentials on the web service.
4. Verify those mail variables synced to worker and cron through the blueprint.
5. Open the worker logs and confirm `queue:work` is running.
6. Open the cron logs and confirm `schedule:run` executes every minute.

## What changed in code

- `config/app.php` now falls back to `RENDER_EXTERNAL_URL`
- `config/database.php` now accepts `DATABASE_URL`
- `app/Providers/AppServiceProvider.php` now forces HTTPS in production/Render

