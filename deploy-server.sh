#!/bin/bash

# Negarin Deployment Script for Fresh Ubuntu Server
# Server IP: 193.36.85.235
# Domain: negarincrafts.com

set -e  # Exit on any error

echo "🚀 Starting Negarin deployment on fresh Ubuntu server..."
echo "Server IP: 193.36.85.235"
echo "Domain: negarincrafts.com"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "This script should not be run as root. Please run as a regular user with sudo privileges."
   exit 1
fi

print_step "1. Updating system packages..."
sudo apt update && sudo apt upgrade -y

print_step "2. Installing essential packages..."
sudo apt install -y \
    curl \
    wget \
    git \
    unzip \
    software-properties-common \
    apt-transport-https \
    ca-certificates \
    gnupg \
    lsb-release \
    ufw \
    htop \
    nano \
    vim

print_step "3. Installing Docker..."
# Remove old Docker versions if any
sudo apt remove -y docker docker-engine docker.io containerd runc 2>/dev/null || true

# Add Docker's official GPG key
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Add Docker repository
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Add current user to docker group
sudo usermod -aG docker $USER

print_step "4. Configuring firewall..."
# Configure UFW firewall
sudo ufw --force reset
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable

print_step "5. Creating application directory..."
APP_DIR="/opt/negarin"
sudo mkdir -p $APP_DIR
sudo chown $USER:$USER $APP_DIR

print_step "6. Setting up directory structure..."
cd $APP_DIR
mkdir -p docker-compose-state/{data,config,overrides}
mkdir -p docker-compose-state/data/{db,redis,negarin/{storage,cache}}
mkdir -p docker-compose-state/config/{proxy,proxy-acme,redis}

print_step "7. Setting proper permissions..."
sudo chown -R $USER:$USER $APP_DIR
chmod -R 755 $APP_DIR

print_step "8. Creating docker-compose.yml..."
cat > docker-compose.yml << 'EOF'
---
###############################################################
# Negarin Docker Compose Configuration
###############################################################

services:
  # HTTP/HTTPS proxy
  proxy:
    image: "nginxproxy/nginx-proxy:${DOCKER_PROXY_VERSION}"
    container_name: "${DOCKER_ALL_CONTAINER_NAME_PREFIX}-proxy"
    restart: unless-stopped
    profiles:
      - ${DOCKER_PROXY_PROFILE:-}
    environment:
      DOCKER_SERVICE_NAME: "proxy"
    volumes:
      - "${DOCKER_PROXY_HOST_DOCKER_SOCKET_PATH}:/tmp/docker.sock:ro"
      - "${DOCKER_ALL_HOST_CONFIG_ROOT_PATH}/proxy/conf.d:/etc/nginx/conf.d"
      - "${DOCKER_ALL_HOST_CONFIG_ROOT_PATH}/proxy/vhost.d:/etc/nginx/vhost.d"
      - "${DOCKER_ALL_HOST_CONFIG_ROOT_PATH}/proxy/certs:/etc/nginx/certs"
      - "${DOCKER_ALL_HOST_DATA_ROOT_PATH}/proxy/html:/usr/share/nginx/html"
    ports:
      - "${DOCKER_PROXY_HOST_PORT_HTTP}:80"
      - "${DOCKER_PROXY_HOST_PORT_HTTPS}:443"
    healthcheck:
      test: "curl --fail https://${APP_DOMAIN}/api/service/health-check"
      interval: "${DOCKER_PROXY_HEALTHCHECK_INTERVAL}"
      retries: 2
      timeout: 5s

  # Proxy companion for managing letsencrypt SSL certificates
  proxy-acme:
    image: nginxproxy/acme-companion
    container_name: "${DOCKER_ALL_CONTAINER_NAME_PREFIX}-proxy-acme"
    restart: unless-stopped
    profiles:
      - ${DOCKER_PROXY_ACME_PROFILE:-}
    environment:
      DEBUG: 0
      DEFAULT_EMAIL: "${DOCKER_PROXY_LETSENCRYPT_EMAIL:?error}"
      NGINX_PROXY_CONTAINER: "${DOCKER_ALL_CONTAINER_NAME_PREFIX}-proxy"
    depends_on:
      - proxy
    volumes:
      - "${DOCKER_ALL_HOST_CONFIG_ROOT_PATH}/proxy-acme:/etc/acme.sh"
      - "${DOCKER_ALL_HOST_CONFIG_ROOT_PATH}/proxy/certs:/etc/nginx/certs"
      - "${DOCKER_ALL_HOST_CONFIG_ROOT_PATH}/proxy/conf.d:/etc/nginx/conf.d"
      - "${DOCKER_ALL_HOST_CONFIG_ROOT_PATH}/proxy/vhost.d:/etc/nginx/vhost.d"
      - "${DOCKER_ALL_HOST_DATA_ROOT_PATH}/proxy/html:/usr/share/nginx/html"
      - "${DOCKER_PROXY_HOST_DOCKER_SOCKET_PATH}:/var/run/docker.sock:ro"

  web:
    image: "${DOCKER_APP_IMAGE}:${DOCKER_APP_TAG}"
    container_name: "${DOCKER_ALL_CONTAINER_NAME_PREFIX}-web"
    restart: unless-stopped
    profiles:
      - ${DOCKER_WEB_PROFILE:-}
    environment:
      # Used by negarin Docker init script
      DOCKER_SERVICE_NAME: "web"
      DOCKER_APP_ENTRYPOINT_DEBUG: ${DOCKER_APP_ENTRYPOINT_DEBUG:-0}
      ENTRYPOINT_SKIP_SCRIPTS: ${ENTRYPOINT_SKIP_SCRIPTS:-}
      # Used by [proxy] service
      LETSENCRYPT_HOST: "${DOCKER_PROXY_LETSENCRYPT_HOST:?error}"
      LETSENCRYPT_EMAIL: "${DOCKER_PROXY_LETSENCRYPT_EMAIL:?error}"
      LETSENCRYPT_TEST: "${DOCKER_PROXY_LETSENCRYPT_TEST:-}"
      VIRTUAL_HOST: "${APP_DOMAIN}"
      VIRTUAL_PORT: "80"
    volumes:
      - "./.env:/var/www/.env"
      - "${DOCKER_ALL_HOST_CONFIG_ROOT_PATH}/proxy/conf.d:/shared/proxy/conf.d"
      - "${DOCKER_APP_HOST_CACHE_PATH}:/var/www/bootstrap/cache"
      - "${DOCKER_APP_HOST_OVERRIDES_PATH}:/docker/overrides:ro"
      - "${DOCKER_APP_HOST_STORAGE_PATH}:/var/www/storage"
    labels:
      com.github.nginx-proxy.nginx-proxy.keepalive: 30
      com.github.nginx-proxy.nginx-proxy.http2.enable: true
      com.github.nginx-proxy.nginx-proxy.http3.enable: true
    ports:
      - "${DOCKER_WEB_PORT_EXTERNAL_HTTP}:80"
    depends_on:
      - db
      - redis
    healthcheck:
      test: 'curl --header "Host: ${APP_DOMAIN}" --fail http://localhost/api/service/health-check'
      interval: "${DOCKER_WEB_HEALTHCHECK_INTERVAL}"
      retries: 2
      timeout: 5s

  worker:
    image: "${DOCKER_APP_IMAGE}:${DOCKER_APP_TAG}"
    container_name: "${DOCKER_ALL_CONTAINER_NAME_PREFIX}-worker"
    command: gosu www-data php artisan horizon
    restart: unless-stopped
    stop_signal: SIGTERM
    profiles:
      - ${DOCKER_WORKER_PROFILE:-}
    environment:
      # Used by negarin Docker init script
      DOCKER_SERVICE_NAME: "worker"
      DOCKER_APP_ENTRYPOINT_DEBUG: ${DOCKER_APP_ENTRYPOINT_DEBUG:-0}
      ENTRYPOINT_SKIP_SCRIPTS: ${ENTRYPOINT_SKIP_SCRIPTS:-}
    volumes:
      - "./.env:/var/www/.env"
      - "${DOCKER_ALL_HOST_CONFIG_ROOT_PATH}/proxy/conf.d:/shared/proxy/conf.d"
      - "${DOCKER_APP_HOST_CACHE_PATH}:/var/www/bootstrap/cache"
      - "${DOCKER_APP_HOST_OVERRIDES_PATH}:/docker/overrides:ro"
      - "${DOCKER_APP_HOST_STORAGE_PATH}:/var/www/storage"
    depends_on:
      - db
      - redis
    healthcheck:
      test: gosu www-data php artisan horizon:status | grep running
      interval: "${DOCKER_WORKER_HEALTHCHECK_INTERVAL:?error}"
      timeout: 5s
      retries: 2

  db:
    image: ${DOCKER_DB_IMAGE:?error}
    container_name: "${DOCKER_ALL_CONTAINER_NAME_PREFIX}-db"
    command: ${DOCKER_DB_COMMAND:-}
    restart: unless-stopped
    profiles:
      - ${DOCKER_DB_PROFILE:-}
    environment:
      TZ: "${TZ:?error}"
      # MySQL (MariaDB)
      MARIADB_ROOT_PASSWORD: "${DOCKER_DB_ROOT_PASSWORD:?error}"
      MARIADB_USER: "${DB_USERNAME:?error}"
      MARIADB_PASSWORD: "${DB_PASSWORD:?error}"
      MARIADB_DATABASE: "${DB_DATABASE:?error}"
    volumes:
      - "${DOCKER_DB_HOST_DATA_PATH:?error}:${DOCKER_DB_CONTAINER_DATA_PATH:?error}"
    ports:
      - "127.0.0.1:3306:3306"
    healthcheck:
      test:
        [
          "CMD",
          "healthcheck.sh",
          "--su-mysql",
          "--connect",
          "--innodb_initialized",
        ]
      interval: "30s"
      retries: 2
      timeout: 5s

  redis:
    image: redis:${DOCKER_REDIS_VERSION}
    container_name: "${DOCKER_ALL_CONTAINER_NAME_PREFIX}-redis"
    restart: unless-stopped
    command: "redis-server --requirepass '${REDIS_PASSWORD:-}'"
    profiles:
      - ${DOCKER_REDIS_PROFILE:-}
    environment:
      TZ: "${TZ:?error}"
      REDISCLI_AUTH: ${REDIS_PASSWORD:-}
    volumes:
      - "${DOCKER_ALL_HOST_CONFIG_ROOT_PATH}/redis:/etc/redis"
      - "${DOCKER_REDIS_HOST_DATA_PATH}:/data"
    ports:
      - "127.0.0.1:6379:6379"
    healthcheck:
      test: ["CMD", "redis-cli", "-a", "${REDIS_PASSWORD:-}", "ping"]
      interval: "30s"
      retries: 2
      timeout: 5s
EOF

print_step "9. Creating environment configuration..."
cat > .env << 'EOF'
#!/bin/bash
# -*- mode: bash -*-
# vi: ft=bash
# shellcheck disable=SC2034,SC2148

################################################################################
# app
################################################################################

APP_NAME="Negarin Crafts"
APP_DOMAIN="negarincrafts.com"
APP_URL="https://${APP_DOMAIN}"
ADMIN_DOMAIN="${APP_DOMAIN}"
APP_TIMEZONE="UTC"
ENABLE_CONFIG_CACHE="true"
OPEN_REGISTRATION="true"
INSTANCE_CONTACT_EMAIL="admin@negarincrafts.com"

################################################################################
# database
################################################################################

DB_VERSION="11.2"
DB_CONNECTION="mysql"
DB_HOST="db"
DB_USERNAME="negarin"
DB_PASSWORD="negarin_secure_2024!"
DB_DATABASE="negarin_prod"
DB_PORT="3306"
DB_APPLY_NEW_MIGRATIONS_AUTOMATICALLY="false"

################################################################################
# mail
################################################################################

MAIL_DRIVER="log"

################################################################################
# redis
################################################################################

REDIS_HOST="redis"
REDIS_PASSWORD="redis_secure_2024!"
REDIS_PORT="6379"

################################################################################
# cache & queue
################################################################################

CACHE_DRIVER="redis"
BROADCAST_DRIVER="redis"
QUEUE_DRIVER="redis"
SESSION_DRIVER="redis"

################################################################################
# ActivityPub
################################################################################

ACTIVITY_PUB="true"
AP_REMOTE_FOLLOW="true"
AP_INBOX="true"
AP_OUTBOX="true"

################################################################################
# logging
################################################################################

LOG_CHANNEL="stderr"

################################################################################
# docker shared
################################################################################

APP_KEY=
DOCKER_ALL_CONTAINER_NAME_PREFIX="${APP_DOMAIN}"
DOCKER_ALL_DEFAULT_HEALTHCHECK_INTERVAL="10s"
DOCKER_ALL_HOST_ROOT_PATH="./docker-compose-state"
DOCKER_ALL_HOST_DATA_ROOT_PATH="${DOCKER_ALL_HOST_ROOT_PATH}/data"
DOCKER_ALL_HOST_CONFIG_ROOT_PATH="${DOCKER_ALL_HOST_ROOT_PATH}/config"
DOCKER_APP_HOST_OVERRIDES_PATH="${DOCKER_ALL_HOST_ROOT_PATH}/overrides"
TZ="${APP_TIMEZONE}"

################################################################################
# docker app
################################################################################

DOCKER_APP_RELEASE="v0.12"
DOCKER_APP_PHP_VERSION="8.3"
DOCKER_APP_RUNTIME="apache"
DOCKER_APP_DEBIAN_RELEASE="bookworm"
DOCKER_APP_BASE_TYPE="apache"
DOCKER_APP_IMAGE="ghcr.io/jippi/docker-pixelfed"
DOCKER_APP_TAG="${DOCKER_APP_RELEASE}-${DOCKER_APP_RUNTIME}-${DOCKER_APP_PHP_VERSION}"
DOCKER_APP_HOST_STORAGE_PATH="${DOCKER_ALL_HOST_DATA_ROOT_PATH}/negarin/storage"
DOCKER_APP_HOST_CACHE_PATH="${DOCKER_ALL_HOST_DATA_ROOT_PATH}/negarin/cache"

################################################################################
# docker redis
################################################################################

DOCKER_REDIS_VERSION="7.2"
DOCKER_REDIS_HOST_DATA_PATH="${DOCKER_ALL_HOST_DATA_ROOT_PATH}/redis"
DOCKER_REDIS_HOST_PORT="${REDIS_PORT}"
DOCKER_REDIS_HEALTHCHECK_INTERVAL="${DOCKER_ALL_DEFAULT_HEALTHCHECK_INTERVAL}"

################################################################################
# docker db
################################################################################

DOCKER_DB_IMAGE="mariadb:${DB_VERSION}"
DOCKER_DB_COMMAND="--default-authentication-plugin=mysql_native_password"
DOCKER_DB_HOST_DATA_PATH="${DOCKER_ALL_HOST_DATA_ROOT_PATH}/db"
DOCKER_DB_CONTAINER_DATA_PATH="/var/lib/mysql"
DOCKER_DB_HOST_PORT="${DB_PORT}"
DOCKER_DB_CONTAINER_PORT="${DB_PORT}"
DOCKER_DB_ROOT_PASSWORD="${DB_PASSWORD}"
DOCKER_DB_HEALTHCHECK_INTERVAL="${DOCKER_ALL_DEFAULT_HEALTHCHECK_INTERVAL}"

################################################################################
# docker web
################################################################################

DOCKER_WEB_PORT_EXTERNAL_HTTP="8080"
DOCKER_WEB_HEALTHCHECK_INTERVAL="${DOCKER_ALL_DEFAULT_HEALTHCHECK_INTERVAL}"

################################################################################
# docker worker
################################################################################

DOCKER_WORKER_HEALTHCHECK_INTERVAL="${DOCKER_ALL_DEFAULT_HEALTHCHECK_INTERVAL}"

################################################################################
# docker proxy
################################################################################

DOCKER_PROXY_VERSION="1.6"
DOCKER_PROXY_HEALTHCHECK_INTERVAL="${DOCKER_ALL_DEFAULT_HEALTHCHECK_INTERVAL}"
DOCKER_PROXY_HOST_PORT_HTTP="80"
DOCKER_PROXY_HOST_PORT_HTTPS="443"
DOCKER_PROXY_HOST_DOCKER_SOCKET_PATH="/var/run/docker.sock"
DOCKER_PROXY_LETSENCRYPT_HOST="${APP_DOMAIN}"
DOCKER_PROXY_LETSENCRYPT_EMAIL="${INSTANCE_CONTACT_EMAIL}"
EOF

print_step "10. Generating application key..."
# Generate a random 32-character key for Laravel
APP_KEY=$(openssl rand -base64 32)
sed -i "s/APP_KEY=/APP_KEY=base64:$APP_KEY/" .env

print_step "11. Starting Docker services..."
# Start Docker service
sudo systemctl enable docker
sudo systemctl start docker

print_warning "You need to log out and log back in for Docker group changes to take effect."
print_warning "Alternatively, you can run: newgrp docker"

# Use newgrp to apply group changes in current session
newgrp docker << EOFGROUP

print_step "12. Pulling Docker images..."
docker compose pull

print_step "13. Starting services..."
docker compose up -d

print_step "14. Waiting for services to be ready..."
sleep 30

print_step "15. Running initial setup..."
# Generate application key if not set
docker compose exec -T web php artisan key:generate --force

# Run database migrations
docker compose exec -T web php artisan migrate --force

# Create storage link
docker compose exec -T web php artisan storage:link

# Clear caches
docker compose exec -T web php artisan config:clear
docker compose exec -T web php artisan cache:clear
docker compose exec -T web php artisan view:clear

# Set proper permissions
docker compose exec -T web chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EOFGROUP

print_step "16. Creating management scripts..."

# Create start script
cat > start.sh << 'EOF'
#!/bin/bash
cd /opt/negarin
docker compose up -d
echo "Negarin services started!"
docker compose ps
EOF

# Create stop script
cat > stop.sh << 'EOF'
#!/bin/bash
cd /opt/negarin
docker compose down
echo "Negarin services stopped!"
EOF

# Create status script
cat > status.sh << 'EOF'
#!/bin/bash
cd /opt/negarin
echo "=== Service Status ==="
docker compose ps
echo ""
echo "=== Service Logs (last 20 lines) ==="
docker compose logs --tail=20
EOF

# Create backup script
cat > backup.sh << 'EOF'
#!/bin/bash
BACKUP_DIR="/opt/negarin-backups"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="negarin_backup_$DATE.tar.gz"

mkdir -p $BACKUP_DIR

echo "Creating backup..."
cd /opt/negarin

# Stop services
docker compose down

# Create backup
tar -czf "$BACKUP_DIR/$BACKUP_FILE" \
    docker-compose-state/ \
    .env \
    docker-compose.yml

# Start services
docker compose up -d

echo "Backup created: $BACKUP_DIR/$BACKUP_FILE"
EOF

chmod +x *.sh

print_step "17. Setting up log rotation..."
sudo tee /etc/logrotate.d/negarin << 'EOF'
/opt/negarin/docker-compose-state/data/*/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 root root
}
EOF

print_step "18. Creating systemd service..."
sudo tee /etc/systemd/system/negarin.service << 'EOF'
[Unit]
Description=Negarin Social Media Platform
Requires=docker.service
After=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/opt/negarin
ExecStart=/usr/bin/docker compose up -d
ExecStop=/usr/bin/docker compose down
TimeoutStartSec=0

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl daemon-reload
sudo systemctl enable negarin

print_step "19. Final checks..."
sleep 10

echo ""
print_status "🎉 Deployment completed successfully!"
echo ""
print_status "Your Negarin instance should be accessible at:"
print_status "  - https://negarincrafts.com"
print_status "  - http://negarincrafts.com (redirects to HTTPS)"
echo ""
print_status "Management commands:"
print_status "  - Start services: ./start.sh"
print_status "  - Stop services: ./stop.sh"
print_status "  - Check status: ./status.sh"
print_status "  - Create backup: ./backup.sh"
echo ""
print_status "System service:"
print_status "  - sudo systemctl start negarin"
print_status "  - sudo systemctl stop negarin"
print_status "  - sudo systemctl status negarin"
echo ""
print_warning "Important next steps:"
print_warning "1. Make sure your domain negarincrafts.com points to 193.36.85.235"
print_warning "2. Wait a few minutes for SSL certificate generation"
print_warning "3. Configure SMTP settings in .env for email functionality"
print_warning "4. Create your first admin user through the web interface"
echo ""
print_status "Logs can be viewed with: docker compose logs -f"
print_status "Configuration files are in: /opt/negarin"