@echo off
echo ========================================
echo Hotel Management System - Server Startup
echo ========================================
echo.

echo Getting your local IP addresses...
echo.
ipconfig | findstr IPv4
echo.
echo Note: Use the IP address from your main network adapter (usually 192.168.x.x)
echo.
echo.
echo Starting Laravel server on all network interfaces...
echo Server will be accessible at:
echo   - http://localhost:8000 (local)
echo   - http://%LOCAL_IP%:8000 (LAN)
echo.
echo Press Ctrl+C to stop the server
echo.

php artisan serve --host=0.0.0.0 --port=8000
