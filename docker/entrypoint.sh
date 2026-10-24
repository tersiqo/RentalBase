#!/bin/sh
set -e

# Dynamically set Nginx listening port based on Render's $PORT env (defaults to 80)
PORT="${PORT:-80}"
echo "Configuring Nginx to listen on port $PORT..."
sed -i "s/listen [0-9]\+;/listen ${PORT};/g" /etc/nginx/http.d/default.conf

# Recreate storage symlink if needed
php artisan storage:link --force || true

# Safe migration: ONLY run php artisan migrate --force if RUN_MIGRATIONS=true
# NEVER run migrate:fresh or --seed to protect Supabase data
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running safe migrations: php artisan migrate --force..."
    php artisan migrate --force
else
    echo "Skipping migrations (RUN_MIGRATIONS is not set to 'true')."
fi

# Optimization caches for production if APP_KEY is provided
if [ -n "$APP_KEY" ]; then
    echo "Caching configuration and routes..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "Starting Supervisord (Nginx + PHP-FPM)..."
exec /usr/bin/supervisord -n -c /etc/supervisord.conf
