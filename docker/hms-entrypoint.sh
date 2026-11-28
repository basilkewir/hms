#!/bin/bash
set -e
cd /var/www/html

for i in $(seq 1 30); do
    php -r "new PDO('mysql:host=' . getenv('DB_HOST') . ';port=3306;dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" >/dev/null 2>&1 && break
    sleep 2
done

chown -R www-data:www-data storage bootstrap/cache database
chmod -R 775 storage bootstrap/cache database

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

php artisan migrate --force || true
php artisan db:seed --force || true
php artisan storage:link || true
php artisan optimize:clear || true

exec supervisord -c /etc/supervisor/conf.d/xcvm.conf
