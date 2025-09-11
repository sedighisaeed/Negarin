#!/bin/bash

# Automated Negarin Deployment Script
# Run this on your server after cloning the repo

set -e  # Exit on any error

echo "🚀 Starting automated Negarin deployment..."
echo "Domain: negarincrafts.com"
echo "Server IP: 193.36.85.235"
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

print_step() {
    echo -e "${GREEN}[STEP]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f ".env.docker" ]; then
    print_error "This script must be run from the Negarin repository directory"
    print_error "Make sure you've cloned the repo and are in the project root"
    exit 1
fi

print_step "1. Preparing environment configuration..."
# Copy .env.docker to .env if .env doesn't exist
if [ ! -f ".env" ]; then
    cp .env.docker .env
    echo "Created .env from .env.docker"
else
    echo ".env already exists, keeping current configuration"
fi

# Generate application key if not set
print_step "2. Checking application key..."
if ! grep -q "^APP_KEY=base64:" .env; then
    APP_KEY=$(openssl rand -base64 32)
    if grep -q "^APP_KEY=" .env; then
        sed -i "s|^APP_KEY=.*|APP_KEY=base64:$APP_KEY|" .env
    else
        echo "APP_KEY=base64:$APP_KEY" >> .env
    fi
    echo "Generated new application key"
else
    echo "Application key already set"
fi

print_step "3. Updating system packages..."
sudo apt update && sudo apt upgrade -y

print_step "4. Installing Docker..."
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker $USER
    rm get-docker.sh
else
    echo "Docker already installed"
fi

# Install Docker Compose plugin
sudo apt install -y docker-compose-plugin

print_step "5. Configuring firewall..."
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable

print_step "6. Creating required directories..."
mkdir -p docker-compose-state/{data/{db,redis,pixelfed/{storage,cache}},config/{proxy,proxy-acme,redis}}
chmod -R 755 docker-compose-state/

print_step "6.1. Downloading Vazir font for offline use..."
# Download Vazir font
chmod +x download-vazir-font.sh
./download-vazir-font.sh

print_step "7. Starting Docker service..."
sudo systemctl enable docker
sudo systemctl start docker

# Apply docker group changes
print_step "8. Applying Docker group permissions..."
if groups $USER | grep -q docker; then
    echo "User already in docker group"
else
    print_warning "Adding user to docker group. You may need to logout/login after deployment."
    sudo usermod -aG docker $USER
fi

print_step "9. Installing unzip (required for font extraction)..."
sudo apt install -y unzip

print_step "10. Fixing .env file format..."
# Fix any malformed environment variables by removing and re-adding quotes properly
sed -i 's/APP_NAME=.*/APP_NAME="Negarin Crafts"/' .env
sed -i 's/APP_DOMAIN=.*/APP_DOMAIN="negarincrafts.com"/' .env
sed -i 's/INSTANCE_CONTACT_EMAIL=.*/INSTANCE_CONTACT_EMAIL="admin@negarincrafts.com"/' .env

# Remove any problematic characters (smart quotes, etc.)
sed -i 's/[""]/"/g' .env

print_step "11. Pulling Docker images..."
docker compose pull

print_step "12. Starting services..."
docker compose up -d

print_step "13. Waiting for services to initialize..."
echo "Waiting 60 seconds for database to be ready..."
sleep 60

print_step "14. Checking service status..."
docker compose ps

print_step "15. Running application setup..."
# Generate application key (backup)
docker compose exec -T web php artisan key:generate --force

# Run database migrations
echo "Running database migrations..."
docker compose exec -T web php artisan migrate --force

# Create storage symlink
echo "Creating storage symlink..."
docker compose exec -T web php artisan storage:link

# Clear caches
echo "Clearing caches..."
docker compose exec -T web php artisan config:clear
docker compose exec -T web php artisan cache:clear
docker compose exec -T web php artisan view:clear

# Set proper permissions
echo "Setting proper permissions..."
docker compose exec -T web chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

print_step "16. Final status check..."
docker compose ps

print_step "17. Testing web service..."
sleep 10
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 | grep -q "200\|301\|302"; then
    echo "✅ Web service is responding"
else
    print_warning "Web service may still be starting up"
fi

echo ""
echo "🎉 Deployment completed!"
echo ""
echo "📋 Next steps:"
echo "1. Make sure your domain negarincrafts.com points to this server (193.36.85.235)"
echo "2. Wait 5-10 minutes for SSL certificate generation"
echo "3. Visit https://negarincrafts.com to access your site"
echo "4. Register your first account and make it admin with:"
echo "   docker compose exec web php artisan user:admin your-username"
echo ""
echo "🔧 Useful commands:"
echo "  docker compose ps              # Check service status"
echo "  docker compose logs -f         # View logs"
echo "  docker compose restart         # Restart services"
echo "  docker compose logs proxy-acme # Check SSL certificate generation"
echo ""
echo "📁 Your Negarin instance is running from: $(pwd)"
echo "🌐 Once DNS propagates, access your site at: https://negarincrafts.com"

# Show current status
echo ""
echo "Current service status:"
docker compose ps