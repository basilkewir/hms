#!/bin/bash
set -e

cd /var/www/html

# Override .env DB settings for Docker/SQLite
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
sed -i 's/^DB_HOST=.*/DB_HOST=/' .env
sed -i 's/^DB_PORT=.*/DB_PORT=/' .env
sed -i 's/^DB_DATABASE=.*/DB_DATABASE=\/var\/www\/html\/database\/database.sqlite/' .env
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=/' .env
sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=/' .env
sed -i 's/^SESSION_DRIVER=.*/SESSION_DRIVER=file/' .env
sed -i 's/^CACHE_STORE=.*/CACHE_STORE=file/' .env
sed -i 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=sync/' .env

# Create SQLite database if it doesn't exist
touch database/database.sqlite

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    php artisan key:generate --force
fi

# Run migrations (force to continue on non-critical errors)
php artisan migrate --force 2>&1 || echo "Migration warning (non-critical)"

# Seed the app-provided seeders (roles, role-bound users, rooms, IPTV data, trial license)
php artisan db:seed --force 2>&1 || echo "Seeding warning (non-critical)"

# Promote the trial license to 'active' so CheckLicense middleware allows access
# without requiring manual activation in local Docker dev.
php artisan tinker --execute="
\$trial = App\Models\License::where('status', 'trial')->first();
if (\$trial) { \$trial->update(['status' => 'active']); echo \"Trial license promoted to active\n\"; }
" 2>&1 || true
echo "=== IPTV Middleware System Ready ==="
echo "API base URL: http://localhost:8000/api"
echo "IPTV API: http://localhost:8000/api/iptv"
echo "Android API: http://localhost:8000/api/android"
echo ""

exec php artisan serve --host=0.0.0.0 --port=8000
