@echo off
echo ========================================
echo Admin Dashboard Fix Verification
echo ========================================
echo.

echo Step 1: Clearing caches...
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo.

echo Step 2: Verifying database connection...
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'Database: Connected' . PHP_EOL; } catch (Exception $e) { echo 'Database: Error - ' . $e->getMessage() . PHP_EOL; }"
echo.

echo Step 3: Checking dashboard data...
php artisan tinker --execute="echo 'Total Rooms: ' . \App\Models\Room::count() . PHP_EOL; echo 'Occupied Rooms: ' . \App\Models\Room::whereIn('status', ['occupied', 'checked_in'])->count() . PHP_EOL; echo 'Total Guests: ' . \App\Models\Guest::count() . PHP_EOL; echo 'Total Reservations: ' . \App\Models\Reservation::count() . PHP_EOL; echo 'All-Time Revenue: ' . number_format(\DB::table('payments')->where('status', 'completed')->sum('amount'), 2) . ' FCFA' . PHP_EOL;"
echo.

echo Step 4: Verifying route...
php artisan route:list | findstr "admin.dashboard"
echo.

echo ========================================
echo Verification Complete!
echo ========================================
echo.
echo Next Steps:
echo 1. Start the server: php artisan serve
echo 2. Visit: http://127.0.0.1:8000/admin/dashboard
echo 3. Login with admin credentials
echo 4. Verify all widgets show real data
echo.
pause
