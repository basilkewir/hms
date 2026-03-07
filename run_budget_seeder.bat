@echo off
cd /d "%~dp0"
php artisan db:seed --class=BudgetPermissionsSeeder
pause
