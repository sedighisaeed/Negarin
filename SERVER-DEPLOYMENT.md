# Negarin Server Deployment Configuration

## Overview

This document outlines the configuration changes made to deploy the Negarin application on an Ubuntu server with Docker. The configuration has been set up to allow access via both the domain name (negarincrafts.com) and the server IP address (193.36.85.235).

## Configuration Changes

### 1. Environment Settings

The following changes were made to the `.env.docker` file:

- Set `APP_ENV="production"` to run the application in production mode
- Set `APP_DEBUG="false"` to disable debug mode in production
- Updated `DOCKER_PROXY_LETSENCRYPT_HOST="${APP_DOMAIN},193.36.85.235"` to include both domain and IP for SSL certificate

### 2. Docker Compose Configuration

The following changes were made to the `docker-compose.yml` file:

- Updated `VIRTUAL_HOST: "${APP_DOMAIN},193.36.85.235"` to allow access via both domain and IP

## Deployment Instructions

1. Transfer the project files to your Ubuntu server
2. Ensure Docker and Docker Compose are installed on the server
3. Navigate to the project directory
4. Run the following commands to deploy:

```bash
# Build and start the Docker containers
docker-compose up -d

# Verify the containers are running
docker-compose ps
```

5. Test the deployment using the provided test script:

```bash
chmod +x test-server-access.sh
./test-server-access.sh
```

## Troubleshooting

If you encounter issues with the deployment, check the following:

1. Ensure ports 80 and 443 are open on your server firewall
2. Verify that your domain DNS settings point to the server IP (193.36.85.235)
3. Check Docker container logs for any errors:

```bash
docker-compose logs
```

## Additional Notes

- The application is configured to use HTTPS by default
- SSL certificates are automatically managed by Let's Encrypt
- The configuration allows access via both https://negarincrafts.com and https://193.36.85.235