#!/bin/sh
set -e

# Generate application key if missing
if [ -z "$APP_KEY" ]; then
    echo "Warning: APP_KEY is empty. Generating temporary key..."
    php artisan key:generate --force || true
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force || true

echo "Starting FrankenPHP server on port 8000..."
exec "$@"
