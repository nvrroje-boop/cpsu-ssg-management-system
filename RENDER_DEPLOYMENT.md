# Render Deployment

This repo is ready for Render with:

- `Dockerfile` for the Laravel web app
- `render.yaml` for web, worker, cron, and Postgres
- `render.free.yaml` for a reduced free-tier demo deploy
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

## Free Render setup

If you must stay on Render Free, use `render.free.yaml` instead of `render.yaml`.

What it includes:

- one free web service
- one free Postgres database

What it changes for compatibility:

- `QUEUE_CONNECTION=sync`
- `MAIL_MAILER=log`
- no worker
- no cron

Free deploy tradeoffs:

- scheduled announcement processing will not run automatically
- attendance reminder / closing alerts will not run automatically
- queued background work runs during the web request instead
- email is logged instead of sent because free Render blocks SMTP ports `25`, `465`, and `587`
- the free database expires after 30 days

This is suitable for demo/testing, not full production behavior.

## Before you deploy

1. Push this repo to GitHub.
2. In Render, choose `New > Blueprint`.
3. Select this repository.
4. Review the services from `render.yaml`.

If you want the free version:

1. In Render, choose `New > Blueprint`.
2. Point it at this repo.
3. Temporarily rename `render.free.yaml` to `render.yaml`, or paste its contents into the Blueprint editor.
4. Deploy the free web service and free database only.

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

For the free setup, you only need:

- `APP_URL`

Mail is already set to `log` in `render.free.yaml`.

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

If you only want a free demo, use `render.free.yaml`.

## After first deploy

Run this checklist in Render:

1. Open the web service and confirm `/up` is healthy.
2. Confirm the database migrations completed in the deploy logs.
3. Set the mail credentials on the web service.
4. Verify those mail variables synced to worker and cron through the blueprint.
5. Open the worker logs and confirm `queue:work` is running.
6. Open the cron logs and confirm `schedule:run` executes every minute.

For the free setup:

1. Open the web service and confirm `/up` is healthy.
2. Confirm migrations completed in deploy logs.
3. Log in and test the core flows manually.
4. Expect reminder emails and scheduled alerts to be disabled.

## What changed in code

- `config/app.php` now falls back to `RENDER_EXTERNAL_URL`
- `config/database.php` now accepts `DATABASE_URL`
- `app/Providers/AppServiceProvider.php` now forces HTTPS in production/Render
