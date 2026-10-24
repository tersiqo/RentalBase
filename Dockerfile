# ==========================================
# Stage 1: Build Frontend Assets (Vite)
# ==========================================
FROM node:20-alpine AS node_builder

WORKDIR /app

# Install dependencies
COPY package*.json ./
RUN npm ci

# Copy files required for Vite and Tailwind CSS scanning
COPY vite.config.js ./
COPY resources/ ./resources/
COPY app/ ./app/
COPY routes/ ./routes/

# Compile frontend assets
RUN npm run build

# ==========================================
# Stage 2: Production PHP + Nginx Runtime
# ==========================================
FROM php:8.3-fpm-alpine

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
    curl \
    git

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

# Get Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html

# Copy built frontend assets from Stage 1
COPY --from=node_builder /app/public/build /var/www/html/public/build

# Install PHP dependencies (production only)
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

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
