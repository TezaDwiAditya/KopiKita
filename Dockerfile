# =====================================
# Stage 1 - Install PHP Dependencies
# =====================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --prefer-dist \
    --no-interaction \
    --no-scripts


# =====================================
# Stage 2 - Build Frontend
# =====================================
FROM node:22-alpine AS assets

WORKDIR /app

COPY package*.json ./

RUN npm install --no-audit --no-fund

# Copy vendor supaya Filament CSS tersedia
COPY --from=vendor /app/vendor ./vendor

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./

RUN npm run build


# =====================================
# Stage 3 - PHP Apache
# =====================================
FROM php:8.4-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    default-mysql-client \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install \
        bcmath \
        intl \
        mbstring \
        pdo_mysql \
        zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

RUN sed -ri \
    -e 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf

COPY composer.json composer.lock ./

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Gunakan vendor yang sudah dibuat pada stage vendor
COPY --from=vendor /app/vendor ./vendor

COPY . .

# Copy hasil Vite build
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev

COPY docker/apache/000-default.conf \
    /etc/apache2/sites-available/000-default.conf

COPY docker/entrypoint.sh /usr/local/bin/entrypoint

RUN chmod +x /usr/local/bin/entrypoint

RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    database

EXPOSE 80

ENTRYPOINT ["entrypoint"]

CMD ["apache2-foreground"]