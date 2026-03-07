#!/bin/bash

echo "========================================"
echo "Hotel Management System - Server Startup"
echo "========================================"
echo ""

# Get local IP address
if [[ "$OSTYPE" == "linux-gnu"* ]]; then
    # Linux
    LOCAL_IP=$(hostname -I | awk '{print $1}')
elif [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    LOCAL_IP=$(ipconfig getifaddr en0 || ipconfig getifaddr en1)
else
    LOCAL_IP="localhost"
fi

echo "Your local IP address: $LOCAL_IP"
echo ""
echo "Starting Laravel server on all network interfaces..."
echo "Server will be accessible at:"
echo "  - http://localhost:8000 (local)"
echo "  - http://$LOCAL_IP:8000 (LAN)"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

php artisan serve --host=0.0.0.0 --port=8000
