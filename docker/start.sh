#!/bin/sh
set -eu

PORT="${PORT:-10000}"

if [ -z "${APP_KEY:-}" ] && [ -n "${APP_KEY_BASE64:-}" ]; then
    export APP_KEY="base64:${APP_KEY_BASE64}"
fi

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is missing; generating a temporary key for this container."
    export APP_KEY="$(php artisan key:generate --show --no-ansi)"
fi

php artisan storage:link || true
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan migrate --force
php artisan db:seed --class=DefaultAdminSeeder --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port="${PORT}"
