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

# 5) Copy all files first
COPY . .

# 6) Copy and set up start script
COPY docker/start.sh /usr/local/bin/start
RUN chmod +x /usr/local/bin/start

# 7) Create and set permissions for var directory
RUN mkdir -p var/cache var/log && \
    chown -R www-data:www-data var/

# 8) Create cron log directory and add crontab
RUN mkdir -p /var/log/cron && \
    chmod 0777 /var/log/cron

COPY docker/crontab /etc/crontabs/root
RUN chmod 0644 /etc/crontabs/root

# 9) Expose FPM socket port
EXPOSE 9000

# 10) Launch start script
CMD ["/usr/local/bin/start"]