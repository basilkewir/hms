FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    nginx supervisor default-mysql-client procps \
    libpng-dev libonig-dev libxml2-dev libzip-dev libicu-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache \
    && sed -i 's/;clear_env = no/clear_env = no/' /usr/local/etc/php-fpm.d/www.conf \
    && printf '#!/bin/sh\nexec /usr/bin/mysql --skip-ssl "$@"\n' > /usr/local/bin/mysql \
    && chmod +x /usr/local/bin/mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/xcvm.conf

WORKDIR /var/www/html

EXPOSE 80
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/xcvm.conf"]
