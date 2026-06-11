#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ]; then
    echo "ERROR: .env is missing in the container image."
    exit 1
fi

if [ -z "${APP_KEY}" ]; then
  APP_KEY="$(grep -E '^APP_KEY=' .env | head -1 | cut -d= -f2- | tr -d '"' | tr -d "'")"
  export APP_KEY
fi

if [ -z "${APP_KEY}" ]; then
    echo "ERROR: APP_KEY is not set. Use a quoted value in .env.docker (Docker strips unquoted trailing '=')."
    exit 1
fi

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
