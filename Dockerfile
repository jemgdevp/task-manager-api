## Production Dockerfile for Laravel API with nginx + php-fpm + supervisor

FROM composer:2.8 AS composer-stage

WORKDIR /app

# Required by spatie/image and spatie/laravel-medialibrary during composer install
RUN docker-php-ext-install exif

# Install PHP dependencies (without scripts because artisan is not present in this stage)
COPY composer.json composer.lock* /app/
RUN composer install --no-interaction --optimize-autoloader --no-dev --prefer-dist --no-scripts


## node:latest Version (node-stage)
FROM node:latest AS node-stage

## Define working directory
WORKDIR /app

COPY package.json pnpm-lock.yaml ./

## Set up npm (latest version to ensure compatibility with pnpm)
RUN npm install -g npm@latest

## Set up pnpm

RUN npm install -g pnpm

## Install dependencies
RUN pnpm install

COPY ./ .

RUN pnpm run build


FROM php:8.4-fpm-bookworm AS production-stage

ENV APP_ENV=production \
    IS_LARAVEL=true \
    NIXPACKS_PHP_ROOT_DIR=/app/public \
    NIXPACKS_PHP_FALLBACK_PATH=/index.php

WORKDIR /app

# Runtime dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    bash \
    curl \
    git \
    unzip \
    nodejs \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Required PHP extensions for this Laravel app
RUN docker-php-ext-install pdo pdo_mysql bcmath pcntl exif

# Copy application source and production artifacts
COPY . /app
COPY --from=composer-stage /app/vendor /app/vendor
COPY --from=node-stage /app/public/build /app/public/build

# Runtime configs and permissions
RUN mkdir -p /assets/scripts /etc/supervisor/conf.d /var/log/nginx /var/log/supervisor \
    && cp /app/.docker/start.sh /assets/start.sh \
    && cp /app/.docker/supervisord.conf /etc/supervisord.conf \
    && cp /app/.docker/worker-*.conf /etc/supervisor/conf.d/ \
    && cp /app/.docker/nginx.template.conf /assets/nginx.template.conf \
    && cp /app/.docker/php-fpm.conf /assets/php-fpm.conf \
    && cp /app/.docker/prestart.mjs /assets/scripts/prestart.mjs \
    && rm -f /app/bootstrap/cache/*.php \
    && php /app/artisan package:discover --ansi \
    && chmod +x /assets/start.sh /app/.docker/deploy.sh \
    && chown -R www-data:www-data /app \
    && chmod -R 775 /app/storage /app/bootstrap/cache

EXPOSE 80

CMD ["/assets/start.sh"]
