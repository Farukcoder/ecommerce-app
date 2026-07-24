# =============================================================================
# Stage 1: Node.js — Build frontend assets (Vite + TailwindCSS v4)
# =============================================================================
FROM node:20-bookworm-slim AS node-builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY vite.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/

RUN npm run build


# =============================================================================
# Stage 2: Composer — Install PHP production dependencies
# =============================================================================
FROM composer:2.8 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader


# =============================================================================
# Stage 3: Final production image — PHP 8.3-FPM + Nginx
# =============================================================================
FROM php:8.3-fpm-bookworm AS final

# ── System dependencies & Nginx ───────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        curl \
        unzip \
        git \
        libpq-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libicu-dev \
        libgd-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        xml \
        opcache \
    && apt-get autoremove -y \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ── Copy custom PHP & Nginx configs ───────────────────────────────────────────
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN mkdir -p /var/log/supervisor

# ── Application code ──────────────────────────────────────────────────────────
WORKDIR /var/www/html

# Copy vendor (from composer-builder) and app source
COPY --from=composer-builder /app/vendor ./vendor
COPY . .

# Copy built frontend assets (from node-builder)
COPY --from=node-builder /app/public/build ./public/build

# ── Permissions & Storage bootstrap ──────────────────────────────────────────
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Run composer scripts now that the full app is in place
RUN php artisan package:discover --ansi || true

# ── Entrypoint ────────────────────────────────────────────────────────────────
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Render.com uses PORT env var; Nginx listens on 80 by default
EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
