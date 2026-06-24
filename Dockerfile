# ── Stage 1: build frontend assets ───────────────────────────────────────────
FROM node:22-alpine AS assets
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
# public/storage is a symlink not present in build context; create the dir
RUN rm -rf public/storage && mkdir -p public/storage && DOCKER_BUILD=1 npm run build

# ── Stage 2: PHP-FPM application ─────────────────────────────────────────────
FROM php:8.4-fpm-alpine AS app
WORKDIR /var/www/html

# System deps + build tools for PHP extensions
RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    zip \
    unzip \
    shadow \
    $PHPIZE_DEPS

# PHP extensions + Redis PECL (all compiled while build tools are present)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" \
      pdo_mysql \
      mbstring \
      exif \
      pcntl \
      bcmath \
      gd \
      zip \
      intl \
      opcache \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apk del $PHPIZE_DEPS

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy app source
COPY . .

# Copy built frontend assets from stage 1
COPY --from=assets /app/public/build ./public/build

# PHP production deps only
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Storage permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# PHP config
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# Entrypoint: copies public/build to shared volume before starting FPM
COPY docker/php/entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN chmod +x /usr/local/bin/app-entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/app-entrypoint.sh"]
CMD ["php-fpm"]
