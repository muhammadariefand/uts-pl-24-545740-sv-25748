# Base image with explicit pinned version (FrankenPHP on PHP 8.3)
FROM dunglas/frankenphp:1.4.4-php8.3-bookworm

# Install required PHP extensions for Laravel & MySQL
RUN install-php-extensions \
    pdo_mysql \
    bcmath \
    intl \
    zip \
    opcache

WORKDIR /app

# Step 1: Copy Composer dependencies files first for Docker layer caching
COPY composer.json composer.lock ./

# Step 2: Install dependencies without running scripts or generating full autoloader
RUN composer install --no-dev --no-interaction --no-autoloader --no-scripts --prefer-dist

# Step 3: Copy Caddyfile configuration
COPY Caddyfile /etc/caddy/Caddyfile

# Step 4: Copy application source code
COPY . .

# Step 5: Generate optimized autoloader for production
RUN composer dump-autoload --optimize --no-dev

# Step 6: Set correct permissions for Laravel storage and cache
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache && \
    chmod +x /app/docker-entrypoint.sh

# Expose HTTP port for FrankenPHP
EXPOSE 8000

ENV SERVER_NAME=":8000"

ENTRYPOINT ["/app/docker-entrypoint.sh"]

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
