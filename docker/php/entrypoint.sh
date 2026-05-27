#!/bin/sh
set -e

# Copy built frontend assets to shared volume so nginx can serve them
if [ -d /var/www/html/public/build ]; then
    cp -rT /var/www/html/public/build /public_assets
fi

exec docker-php-entrypoint "$@"
