#!/bin/bash

# Local setup script to prepare files for server deployment
# Run this on your local machine before copying to server

echo "🔧 Preparing Negarin files for server deployment..."

# Create deployment directory
mkdir -p negarin-deploy

# Copy essential files
cp .env.docker negarin-deploy/.env
cp docker-compose.yml negarin-deploy/ 2>/dev/null || echo "docker-compose.yml will be created on server"

# Create a simple docker-compose.yml if it doesn't exist
if [ ! -f negarin-deploy/docker-compose.yml ]; then
    echo "Creating docker-compose.yml..."
    cat > negarin-deploy/docker-compose.yml << 'EOF'
---
services:
  proxy:
    image: "nginxproxy/nginx-proxy:${DOCKER_PROXY_VERSION}"
    container_name: "${DOCKER_ALL_CONTAINER_NAME_PREFIX}-proxy"
    restart: unless-stopped
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

  proxy-acme:
    image: nginxproxy/acme-companion
    container_name: "${DOCKER_ALL_CONTAINER_NAME_PREFIX}-proxy-acme"
    restart: unless-stopped
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
    environment:
      DOCKER_SERVICE_NAME: "web"
      LETSENCRYPT_HOST: "${DOCKER_PROXY_LETSENCRYPT_HOST:?error}"
      LETSENCRYPT_EMAIL: "${DOCKER_PROXY_LETSENCRYPT_EMAIL:?error}"
      VIRTUAL_HOST: "${APP_DOMAIN}"
      VIRTUAL_PORT: "80"
    volumes:
      - "./.env:/var/www/.env"
      - "${DOCKER_APP_HOST_CACHE_PATH}:/var/www/bootstrap/cache"
      - "${DOCKER_APP_HOST_STORAGE_PATH}:/var/www/storage"
    ports:
      - "${DOCKER_WEB_PORT_EXTERNAL_HTTP}:80"
    depends_on:
      - db
      - redis

  worker:
    image: "${DOCKER_APP_IMAGE}:${DOCKER_APP_TAG}"
    container_name: "${DOCKER_ALL_CONTAINER_NAME_PREFIX}-worker"
    command: gosu www-data php artisan horizon
    restart: unless-stopped
    environment:
      DOCKER_SERVICE_NAME: "worker"
    volumes:
      - "./.env:/var/www/.env"
      - "${DOCKER_APP_HOST_CACHE_PATH}:/var/www/bootstrap/cache"
      - "${DOCKER_APP_HOST_STORAGE_PATH}:/var/www/storage"
    depends_on:
      - db
      - redis

  db:
    image: ${DOCKER_DB_IMAGE:?error}
    container_name: "${DOCKER_ALL_CONTAINER_NAME_PREFIX}-db"
    command: ${DOCKER_DB_COMMAND:-}
    restart: unless-stopped
    environment:
      TZ: "${TZ:?error}"
      MARIADB_ROOT_PASSWORD: "${DOCKER_DB_ROOT_PASSWORD:?error}"
      MARIADB_USER: "${DB_USERNAME:?error}"
      MARIADB_PASSWORD: "${DB_PASSWORD:?error}"
      MARIADB_DATABASE: "${DB_DATABASE:?error}"
    volumes:
      - "${DOCKER_DB_HOST_DATA_PATH:?error}:${DOCKER_DB_CONTAINER_DATA_PATH:?error}"
    ports:
      - "127.0.0.1:3306:3306"

  redis:
    image: redis:${DOCKER_REDIS_VERSION}
    container_name: "${DOCKER_ALL_CONTAINER_NAME_PREFIX}-redis"
    restart: unless-stopped
    command: "redis-server --requirepass '${REDIS_PASSWORD:-}'"
    environment:
      TZ: "${TZ:?error}"
    volumes:
      - "${DOCKER_REDIS_HOST_DATA_PATH}:/data"
    ports:
      - "127.0.0.1:6379:6379"
EOF
fi

# Copy the deployment script
cp deploy-server.sh negarin-deploy/

# Make scripts executable
chmod +x negarin-deploy/deploy-server.sh

echo "✅ Files prepared in negarin-deploy/ directory"
echo ""
echo "📋 Next steps:"
echo "1. Copy files to your server:"
echo "   scp -r negarin-deploy/* root@193.36.85.235:/tmp/"
echo ""
echo "2. SSH to your server:"
echo "   ssh root@193.36.85.235"
echo ""
echo "3. Run the deployment script:"
echo "   cd /tmp && chmod +x deploy-server.sh && ./deploy-server.sh"
echo ""
echo "🔐 Make sure your domain negarincrafts.com points to 193.36.85.235"