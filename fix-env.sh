#!/bin/bash

echo "🔧 Fixing .env file formatting issues..."

# Fix malformed quotes and characters
sed -i 's/[""]/"/g' .env
sed -i 's/APP_NAME="Negarin Crafts"/APP_NAME="Negarin Crafts"/' .env
sed -i 's/APP_DOMAIN="negarincrafts.com"/APP_DOMAIN="negarincrafts.com"/' .env
sed -i 's/INSTANCE_CONTACT_EMAIL="admin@negarincrafts.com"/INSTANCE_CONTACT_EMAIL="admin@negarincrafts.com"/' .env

# Remove any lines with problematic characters
grep -v 'unexpected character' .env > .env.tmp && mv .env.tmp .env

echo "✅ .env file formatting fixed"

# Test if Docker can read the .env file now
echo "🧪 Testing .env file..."
if docker compose config > /dev/null 2>&1; then
    echo "✅ .env file is now valid"
else
    echo "❌ .env file still has issues"
    echo "Showing problematic lines:"
    docker compose config 2>&1 | grep -A5 -B5 "error\|failed"
fi