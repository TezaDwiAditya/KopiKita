# =====================================
# Stage 1 - PHP Dependencies / Composer
# =====================================
FROM php:8.4-cli AS vendor

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install \
        intl \
        mbstring \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

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

# Filament membutuhkan vendor saat Vite build
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

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

# Gunakan vendor yang sudah berhasil dibuat
COPY --from=vendor /app/vendor ./vendor

COPY . .

# Hasil Vite build
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