#!/bin/bash

# Quick Negarin Deployment Script
# For Ubuntu Server 22.04/20.04

set -e

echo "🚀 Quick Negarin Deployment Starting..."

# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER

# Install Docker Compose
sudo apt install -y docker-compose-plugin

# Setup firewall
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp  
sudo ufw allow 443/tcp
sudo ufw --force enable

# Create app directory
sudo mkdir -p /opt/negarin
sudo chown $USER:$USER /opt/negarin
cd /opt/negarin

# Create directory structure
mkdir -p docker-compose-state/{data/{db,redis,negarin/{storage,cache}},config/{proxy,redis}}

# Create .env file
cat > .env << 'EOF'
APP_NAME="Negarin Crafts"
APP_DOMAIN="negarincrafts.com"
APP_URL="https://negarincrafts.com"
ADMIN_DOMAIN="negarincrafts.com"
APP_TIMEZONE="UTC"
ENABLE_CONFIG_CACHE="true"
OPEN_REGISTRATION="true"
INSTANCE_CONTACT_EMAIL="admin@negarincrafts.com"

DB_VERSION="11.2"
DB_CONNECTION="mysql"
DB_HOST="db"
DB_USERNAME="negarin"
DB_PASSWORD="negarin_secure_2024!"
DB_DATABASE="negarin_prod"
DB_PORT="3306"

REDIS_HOST="redis"
REDIS_PASSWORD="redis_secure_2024!"
REDIS_PORT="6379"

CACHE_DRIVER="redis"
QUEUE_DRIVER="redis"
SESSION_DRIVER="redis"
BROADCAST_DRIVER="redis"

ACTIVITY_PUB="true"
AP_REMOTE_FOLLOW="true"
AP_INBOX="true"
AP_OUTBOX="true"

MAIL_DRIVER="log"
LOG_CHANNEL="stderr"

# Docker Configuration
DOCKER_ALL_CONTAINER_NAME_PREFIX="negarincrafts.com"
DOCKER_ALL_HOST_ROOT_PATH="./docker-compose-state"
DOCKER_ALL_HOST_DATA_ROOT_PATH="./docker-compose-state/data"
DOCKER_ALL_HOST_CONFIG_ROOT_PATH="./docker-compose-state/config"
TZ="UTC"

DOCKER_APP_IMAGE="ghcr.io/jippi/docker-pixelfed"
DOCKER_APP_TAG="v0.12-apache-8.3"
DOCKER_APP_HOST_STORAGE_PATH="./docker-compose-state/data/negarin/storage"
DOCKER_APP_HOST_CACHE_PATH="./docker-compose-state/data/negarin/cache"

DOCKER_DB_IMAGE="mariadb:11.2"
DOCKER_DB_COMMAND="--default-authentication-plugin=mysql_native_password"
DOCKER_DB_HOST_DATA_PATH="./docker-compose-state/data/db"
DOCKER_DB_CONTAINER_DATA_PATH="/var/lib/mysql"
DOCKER_DB_ROOT_PASSWORD="negarin_secure_2024!"

DOCKER_REDIS_VERSION="7.2"
DOCKER_REDIS_HOST_DATA_PATH="./docker-compose-state/data/redis"

DOCKER_PROXY_VERSION="1.6"
DOCKER_PROXY_HOST_PORT_HTTP="80"
DOCKER_PROXY_HOST_PORT_HTTPS="443"
DOCKER_PROXY_HOST_DOCKER_SOCKET_PATH="/var/run/docker.sock"
DOCKER_PROXY_LETSENCRYPT_HOST="negarincrafts.com"
DOCKER_PROXY_LETSENCRYPT_EMAIL="admin@negarincrafts.com"

DOCKER_WEB_PORT_EXTERNAL_HTTP="8080"
EOF

# Generate app key
APP_KEY=$(openssl rand -base64 32)
echo "APP_KEY=base64:$APP_KEY" >> .env

# Create docker-compose.yml
cat > docker-compose.yml << 'EOF'
services:
  proxy:
    image: nginxproxy/nginx-proxy:1.6
    container_name: negarincrafts.com-proxy
    restart: unless-stopped
    volumes:
      - /var/run/docker.sock:/tmp/docker.sock:ro
      - ./docker-compose-state/config/proxy/certs:/etc/nginx/certs
      - ./docker-compose-state/config/proxy/vhost.d:/etc/nginx/vhost.d
      - ./docker-compose-state/data/proxy/html:/usr/share/nginx/html
    ports:
      - "80:80"
      - "443:443"

  proxy-acme:
    image: nginxproxy/acme-companion
    container_name: negarincrafts.com-proxy-acme
    restart: unless-stopped
    environment:
      DEFAULT_EMAIL: admin@negarincrafts.com
      NGINX_PROXY_CONTAINER: negarincrafts.com-proxy
    depends_on:
      - proxy
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - ./docker-compose-state/config/proxy/certs:/etc/nginx/certs
      - ./docker-compose-state/config/proxy/vhost.d:/etc/nginx/vhost.d
      - ./docker-compose-state/data/proxy/html:/usr/share/nginx/html

  web:
    image: ghcr.io/jippi/docker-pixelfed:v0.12-apache-8.3
    container_name: negarincrafts.com-web
    restart: unless-stopped
    environment:
      VIRTUAL_HOST: negarincrafts.com
      LETSENCRYPT_HOST: negarincrafts.com
      LETSENCRYPT_EMAIL: admin@negarincrafts.com
    volumes:
      - ./.env:/var/www/.env
      - ./docker-compose-state/data/negarin/storage:/var/www/storage
      - ./docker-compose-state/data/negarin/cache:/var/www/bootstrap/cache
    depends_on:
      - db
      - redis

  worker:
    image: ghcr.io/jippi/docker-pixelfed:v0.12-apache-8.3
    container_name: negarincrafts.com-worker
    restart: unless-stopped
    command: gosu www-data php artisan horizon
    volumes:
      - ./.env:/var/www/.env
      - ./docker-compose-state/data/negarin/storage:/var/www/storage
      - ./docker-compose-state/data/negarin/cache:/var/www/bootstrap/cache
    depends_on:
      - db
      - redis

  db:
    image: mariadb:11.2
    container_name: negarincrafts.com-db
    restart: unless-stopped
    command: --default-authentication-plugin=mysql_native_password
    environment:
      MARIADB_ROOT_PASSWORD: negarin_secure_2024!
      MARIADB_USER: negarin
      MARIADB_PASSWORD: negarin_secure_2024!
      MARIADB_DATABASE: negarin_prod
    volumes:
      - ./docker-compose-state/data/db:/var/lib/mysql

  redis:
    image: redis:7.2
    container_name: negarincrafts.com-redis
    restart: unless-stopped
    command: redis-server --requirepass redis_secure_2024!
    volumes:
      - ./docker-compose-state/data/redis:/data
EOF

# Start services
echo "🐳 Starting Docker services..."
newgrp docker << 'EOFGROUP'
docker compose pull
docker compose up -d

# Wait for services
sleep 30

# Setup application
echo "⚙️ Setting up application..."
docker compose exec -T web php artisan key:generate --force
docker compose exec -T web php artisan migrate --force
docker compose exec -T web php artisan storage:link
docker compose exec -T web php artisan config:clear

EOFGROUP

echo "✅ Deployment complete!"
echo ""
echo "🌐 Your site will be available at: https://negarincrafts.com"
echo "📧 Make sure your domain points to this server's IP: 193.36.85.235"
echo ""
echo "📋 Useful commands:"
echo "  docker compose ps          # Check status"
echo "  docker compose logs -f     # View logs"
echo "  docker compose restart     # Restart services"
echo ""
echo "⚠️  You may need to log out and back in for Docker permissions to work properly."