#!/usr/bin/env sh
set -eu

RAW_APP_URL="${APP_URL:-${RENDER_EXTERNAL_URL:-http://localhost:${PORT:-10000}}}"
APP_URL="$(printf '%s' "$RAW_APP_URL" | tr -d '\r\n\t')"
export APP_URL

mkdir -p \
    bootstrap/cache \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

php artisan storage:link >/dev/null 2>&1 || true
php artisan migrate --force
php artisan config:clear >/dev/null 2>&1 || true
php artisan config:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
