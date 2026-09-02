#!/usr/bin/env sh
set -e

cd /var/www/html

if [ "${INSTALL_COMPOSER_DEPS:-false}" = "true" ] && [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

if [ -d /usr/src/app-public ]; then
    cp -a /usr/src/app-public/. public/
fi

if [ ! -f .env ]; then
    if [ "${APP_ENV:-local}" = "production" ] && [ -f .env.production ]; then
        cp .env.production .env
    elif [ -f .env.development ]; then
        cp .env.development .env
    elif [ -f .env.docker ]; then
        cp .env.docker .env
    else
        cp .env.example .env
    fi
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database-data

if [ ! -f database-data/database.sqlite ]; then
    touch database-data/database.sqlite
fi

chown -R www-data:www-data storage bootstrap/cache database database-data

if [ "${DB_CONNECTION:-}" = "mysql" ] || [ "${DB_CONNECTION:-}" = "mariadb" ]; then
    echo "Waiting for database at ${DB_HOST:-mysql}:${DB_PORT:-3306}..."
    until php -r '
        $host = getenv("DB_HOST") ?: "mysql";
        $port = getenv("DB_PORT") ?: "3306";
        $db = getenv("DB_DATABASE") ?: "";
        $user = getenv("DB_USERNAME") ?: "root";
        $pass = getenv("DB_PASSWORD") ?: "";
        try {
            new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass);
            exit(0);
        } catch (Throwable $e) {
            exit(1);
        }
    '; do
        sleep 2
    done
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force --no-interaction
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

php artisan storage:link --force || true

if [ "${APP_ENV:-local}" = "production" ]; then
    php artisan optimize --no-interaction
else
    php artisan optimize:clear --no-interaction
fi

exec "$@"
