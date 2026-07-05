#!/usr/bin/env sh
set -e

cd /var/www/html

if [ ! -f public/index.php ] && [ -d /usr/src/app-public ]; then
    cp -a /usr/src/app-public/. public/
fi

if [ ! -f .env ]; then
    if [ -f .env.docker ]; then
        cp .env.docker .env
    else
        cp .env.example .env
    fi
fi

if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

chown -R www-data:www-data storage bootstrap/cache database

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force --no-interaction
fi

php artisan migrate --force --no-interaction
php artisan storage:link --force || true
php artisan optimize:clear --no-interaction

exec "$@"