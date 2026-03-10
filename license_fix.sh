#!/bin/bash

# Quick fix for license signature secret on server
# Run this on the Ubuntu server: bash license_fix.sh

echo "=== HMS License Signature Secret Fix ==="
echo ""

# Get the current APP_KEY from .env
APP_KEY=$(grep "^APP_KEY=" /opt/hms/.env | cut -d'=' -f2)

if [ -z "$APP_KEY" ]; then
    echo "ERROR: Could not find APP_KEY in /opt/hms/.env"
    exit 1
fi

echo "Found APP_KEY: $APP_KEY"
echo ""

# Check if LICENSE_SIGNATURE_SECRET already exists
if grep -q "^LICENSE_SIGNATURE_SECRET=" /opt/hms/.env; then
    echo "LICENSE_SIGNATURE_SECRET already exists, updating..."
    sudo sed -i "s/^LICENSE_SIGNATURE_SECRET=.*/LICENSE_SIGNATURE_SECRET=$APP_KEY/" /opt/hms/.env
else
    echo "Adding LICENSE_SIGNATURE_SECRET to .env..."
    # Add it after LICENSE_TOKEN_EXPIRATION or at the end
    if grep -q "^LICENSE_TOKEN_EXPIRATION=" /opt/hms/.env; then
        sudo sed -i "/^LICENSE_TOKEN_EXPIRATION=/a LICENSE_SIGNATURE_SECRET=$APP_KEY" /opt/hms/.env
    else
        echo "LICENSE_SIGNATURE_SECRET=$APP_KEY" | sudo tee -a /opt/hms/.env > /dev/null
    fi
fi

echo ""
echo "=== Verifying Changes ==="
echo ""
echo "APP_KEY:"
grep "^APP_KEY=" /opt/hms/.env
echo ""
echo "LICENSE_SIGNATURE_SECRET:"
grep "^LICENSE_SIGNATURE_SECRET=" /opt/hms/.env
echo ""

# Clear caches
echo "=== Clearing Laravel Caches ==="
cd /opt/hms
php artisan config:clear
php artisan cache:clear

echo ""
echo "✓ License signature secret fixed!"
echo ""
echo "Next steps:"
echo "1. Refresh your browser: https://192.168.20.85 (or your domain)"
echo "2. Try license activation again with your license key"
echo ""
