#!/bin/sh
set -e

# Start cron in background
crond -b -l 8

# Start PHP-FPM
exec php-fpm 