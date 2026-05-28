#!/bin/sh
set -e

# Ensure required storage subdirectories exist
mkdir -p /var/www/html/storage/app/private/livewire-tmp
mkdir -p /var/www/html/storage/app/private/framework/cache
mkdir -p /var/www/html/storage/app/private/framework/sessions
mkdir -p /var/www/html/storage/app/private/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/public/storage

# Fix permissions on bind-mounted storage directories
chown -R www-data:www-data /var/www/html/storage /var/www/html/public/storage 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/public/storage 2>/dev/null || true

# Copy built frontend assets to shared volume so nginx can serve them
if [ -d /var/www/html/public/build ]; then
    cp -rT /var/www/html/public/build /public_assets
fi

exec docker-php-entrypoint "$@"
