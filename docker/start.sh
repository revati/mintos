#!/bin/sh
composer install --optimize-autoloader --classmap-authoritative --no-interaction

set -e

# Start cron in background
crond -b -l 8

# Start PHP-FPM
exec php-fpm 