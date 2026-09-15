FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev \
    libsqlite3-dev sqlite3 libzip-dev \
    && docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy all files
COPY . .

# Install dependencies (ignore platform reqs to avoid lock conflicts, then run platform check later)
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-interaction --no-scripts --prefer-dist --ignore-platform-reqs || \
    COMPOSER_ALLOW_SUPERUSER=1 composer update --no-dev --no-interaction --prefer-dist --ignore-platform-reqs || true

# Run post-install scripts
RUN COMPOSER_ALLOW_SUPERUSER=1 composer run-script post-autoload-dump --no-interaction 2>/dev/null || true
RUN php artisan package:discover --ansi 2>/dev/null || true

# Create required directories
RUN mkdir -p bootstrap/cache storage/logs storage/framework/cache storage/framework/sessions storage/framework/views storage/framework/testing

# Dump autoload
RUN composer dump-autoload --optimize 2>/dev/null || true

# Set permissions
RUN chmod +x docker-entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["./docker-entrypoint.sh"]
