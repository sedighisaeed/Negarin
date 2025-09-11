# Technology Stack

## Backend Framework
- **Laravel 12.x** - PHP web application framework
- **PHP 8.2/8.3/8.4** - Server-side language
- **Composer** - PHP dependency management

## Frontend
- **Vue.js 2.6** - JavaScript framework for UI components
- **Laravel Mix** - Asset compilation and bundling
- **Bootstrap 4** - CSS framework
- **Sass** - CSS preprocessor
- **Webpack** - Module bundler

## Database & Caching
- **MySQL/MariaDB/PostgreSQL** - Primary database
- **Redis** - Caching and queue backend
- **Laravel Horizon** - Queue monitoring and management

## Key Dependencies
- **Laravel Passport** - OAuth2 server implementation
- **Intervention Image** - Image processing
- **Laravel FFmpeg** - Video processing
- **Spatie Laravel Backup** - Backup management
- **ActivityPub** - Federation protocol support

## Development Tools
- **Pest** - PHP testing framework
- **Laravel Pint** - Code style fixer
- **Laravel Telescope** - Debug assistant (dev only)

## Common Commands

### Development
```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Build frontend assets
npm run development
npm run watch
npm run production

# Run tests
./vendor/bin/pest

# Code formatting
./vendor/bin/pint
```

### Laravel Artisan
```bash
# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Queue management
php artisan horizon
php artisan queue:work

# Storage link
php artisan storage:link
```

### Docker
```bash
# Build and start services
docker-compose up -d

# View logs
docker-compose logs -f web

# Execute commands in container
docker-compose exec web php artisan migrate
```

## Environment Configuration
- Use `.env` file for environment-specific settings
- Copy `.env.example` to `.env` for initial setup
- Key settings: database, Redis, mail, storage, ActivityPub