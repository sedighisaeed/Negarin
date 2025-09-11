# Negarin Deployment Guide

Deploy Negarin on your Ubuntu server (193.36.85.235) with domain negarincrafts.com

## Quick Deployment (Recommended)

### Step 1: Copy files to server
```bash
# On your local machine
scp quick-deploy.sh root@193.36.85.235:/tmp/
```

### Step 2: Run deployment on server
```bash
# SSH to your server
ssh root@193.36.85.235

# Run the deployment script
cd /tmp
chmod +x quick-deploy.sh
./quick-deploy.sh
```

## Manual Deployment

If you prefer to run commands manually:

### 1. Update System
```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Install Docker
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER
sudo apt install -y docker-compose-plugin
```

### 3. Setup Firewall
```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp  
sudo ufw allow 443/tcp
sudo ufw --force enable
```

### 4. Create Application Directory
```bash
sudo mkdir -p /opt/negarin
sudo chown $USER:$USER /opt/negarin
cd /opt/negarin
```

### 5. Copy Configuration Files
Copy the `.env` and `docker-compose.yml` files from the quick-deploy.sh script to `/opt/negarin/`

### 6. Start Services
```bash
# You may need to log out and back in for Docker group changes
newgrp docker
docker compose up -d
```

### 7. Initialize Application
```bash
# Wait for services to start
sleep 30

# Run setup commands
docker compose exec web php artisan key:generate --force
docker compose exec web php artisan migrate --force
docker compose exec web php artisan storage:link
docker compose exec web php artisan config:clear
```

## Post-Deployment

### DNS Configuration
Make sure your domain `negarincrafts.com` points to `193.36.85.235`:
- A record: `negarincrafts.com` → `193.36.85.235`
- CNAME record: `www.negarincrafts.com` → `negarincrafts.com`

### SSL Certificate
The Let's Encrypt certificate will be automatically generated. Wait 5-10 minutes after DNS propagation.

### First Admin User
1. Visit https://negarincrafts.com
2. Register your first account
3. Make it admin via database or artisan command:
```bash
docker compose exec web php artisan user:admin username
```

## Management Commands

### Service Management
```bash
cd /opt/negarin

# Check status
docker compose ps

# View logs
docker compose logs -f

# Restart services
docker compose restart

# Stop services
docker compose down

# Start services
docker compose up -d
```

### Application Commands
```bash
# Clear caches
docker compose exec web php artisan cache:clear
docker compose exec web php artisan config:clear

# Run migrations
docker compose exec web php artisan migrate

# Check queue status
docker compose exec web php artisan horizon:status
```

## Troubleshooting

### Services not starting
```bash
# Check logs
docker compose logs

# Check system resources
df -h
free -h
```

### SSL certificate issues
```bash
# Check certificate generation
docker compose logs proxy-acme

# Manually trigger certificate
docker compose exec proxy-acme /app/signal_le_service
```

### Database connection issues
```bash
# Check database logs
docker compose logs db

# Test database connection
docker compose exec web php artisan tinker
# Then run: DB::connection()->getPdo();
```

## Configuration Files

### Main Configuration (.env)
Located at `/opt/negarin/.env` - contains all application settings

### Docker Compose (docker-compose.yml)
Located at `/opt/negarin/docker-compose.yml` - defines services

### Data Storage
- Database: `/opt/negarin/docker-compose-state/data/db`
- Redis: `/opt/negarin/docker-compose-state/data/redis`
- Uploads: `/opt/negarin/docker-compose-state/data/negarin/storage`
- Cache: `/opt/negarin/docker-compose-state/data/negarin/cache`

## Security Notes

1. **Change default passwords** in `.env` file
2. **Setup proper SMTP** for email notifications
3. **Regular backups** of the data directory
4. **Monitor logs** for suspicious activity
5. **Keep Docker images updated**

## Backup

```bash
# Create backup
cd /opt/negarin
docker compose down
tar -czf negarin-backup-$(date +%Y%m%d).tar.gz docker-compose-state/ .env docker-compose.yml
docker compose up -d
```

## Support

- Check logs: `docker compose logs -f`
- Negarin documentation: https://docs.negarin.org
- Docker documentation: https://docs.docker.com