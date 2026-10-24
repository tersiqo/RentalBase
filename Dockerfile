# ==========================================
# Stage 1: Vendor Dependencies (Composer)
# ==========================================
FROM composer:2 AS composer_builder

WORKDIR /app

# Copy composer definitions and install packages
COPY composer*.json ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --ignore-platform-reqs

# Copy application files and generate optimized autoload
COPY . .
RUN composer dump-autoload --optimize --no-dev --ignore-platform-reqs

# ==========================================
# Stage 2: Build Frontend Assets (Vite)
# ==========================================
FROM node:22-alpine AS node_builder

WORKDIR /app

# Install npm dependencies
COPY package*.json ./
RUN npm ci

# Copy files required for Vite and Tailwind CSS scanning
COPY vite.config.js ./
COPY resources/ ./resources/
COPY app/ ./app/
COPY routes/ ./routes/
COPY public/ ./public/

# Copy vendor from composer_builder so Livewire ESM and Tailwind sources are available
COPY --from=composer_builder /app/vendor ./vendor

# Compile frontend assets
RUN npm run build

# ==========================================
# Stage 3: Production PHP + Nginx Runtime
# ==========================================
FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

# Install required system packages
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    bash \
    curl

# Configure & install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        gd \
        bcmath \
        intl \
        opcache \
        pcntl

# Use production PHP settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copy application source code
COPY . /var/www/html

# Copy vendor from composer_builder
COPY --from=composer_builder /app/vendor /var/www/html/vendor

# Copy built frontend assets from node_builder
COPY --from=node_builder /app/public/build /var/www/html/public/build

# Configure Nginx, Supervisor, and startup script
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Ensure storage and bootstrap directories exist with write permissions
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
