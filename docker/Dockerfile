# docker/Dockerfile
ARG PHP_VERSION=8.4-fpm-alpine
FROM php:${PHP_VERSION}

# 1) System dependencies
RUN apk add --no-cache \
    icu-dev \
    oniguruma-dev \
    libzip-dev \
    zip \
    unzip \
    bash \
    git \
    postgresql-dev \
    dcron

# 2) PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    intl \
    zip \
    opcache

# 3) Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4) Set working dir
WORKDIR /app

# 5) Build-time install of PHP deps
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --optimize-autoloader \
    --classmap-authoritative \
    --no-interaction

# 6) Copy application code
COPY . .

# 7) Copy and set up start script
COPY docker/start.sh /usr/local/bin/start
RUN chmod +x /usr/local/bin/start

# 8) Ensure www-data can write cache/logs
RUN chown -R www-data:www-data var/

# 9) Create cron log directory and add crontab
RUN mkdir -p /var/log/cron && \
    chmod 0777 /var/log/cron

COPY docker/crontab /etc/crontabs/root
RUN chmod 0644 /etc/crontabs/root

# 10) Expose FPM socket port
EXPOSE 9000

# 11) Launch PHP-FPM
CMD ["php-fpm"]