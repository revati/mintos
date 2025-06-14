#!/bin/sh
set -e

# Start messenger consumer in the background
php bin/console messenger:consume --time-limit=3600 async scheduler_default &

# Start scheduler in the background
php bin/console scheduler:start &

# Start PHP-FPM in the foreground
php-fpm 