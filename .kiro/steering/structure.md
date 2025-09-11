# Project Structure

## Root Directory
- `artisan` - Laravel command-line interface
- `composer.json/lock` - PHP dependencies
- `package.json/lock` - Node.js dependencies
- `webpack.mix.js` - Asset compilation configuration
- `docker-compose.yml` - Docker orchestration
- `Dockerfile` - Container build instructions

## Application Structure (`app/`)
Standard Laravel application structure with domain models:

### Core Models
- `User.php` - User accounts and authentication
- `Profile.php` - User profiles and metadata
- `Status.php` - Posts/photos with media attachments
- `Media.php` - File uploads and media processing
- `Follower.php` - Social graph relationships
- `Activity.php` - ActivityPub federation events

### HTTP Layer (`app/Http/`)
- `Controllers/` - Request handling and business logic
- `Middleware/` - Request/response filtering
- `Requests/` - Form validation rules
- `Resources/` - API response transformers

### Supporting Services
- `Jobs/` - Background queue processing
- `Mail/` - Email notifications
- `Services/` - Business logic services
- `Observers/` - Model event handlers
- `Policies/` - Authorization rules

## Configuration (`config/`)
Laravel configuration files with Negarin-specific additions:
- `pixelfed.php` - Core application settings
- `federation.php` - ActivityPub configuration
- `media.php` - Image/video processing settings
- `instance.php` - Instance-specific configuration

## Frontend Assets (`resources/`)
- `assets/js/` - Vue.js components and JavaScript
- `assets/sass/` - Styling and themes
- `views/` - Blade templates
- `lang/` - Internationalization files

## Routes (`routes/`)
- `web.php` - Main web interface routes
- `api.php` - Public API endpoints
- `web-api.php` - Internal AJAX endpoints
- `web-admin.php` - Admin panel routes

## Database (`database/`)
- `migrations/` - Database schema changes
- `seeds/` - Sample data for development
- `factories/` - Test data generation

## Public Assets (`public/`)
- Compiled CSS/JS assets
- Static images and fonts
- Entry point (`index.php`)

## Storage (`storage/`)
- `app/` - User uploads and generated files
- `logs/` - Application logs
- `framework/` - Laravel cache and sessions

## Docker (`docker/`)
- Multi-stage Dockerfile configurations
- Runtime-specific configurations (Apache, Nginx, FPM)
- Shared scripts and templates

## Development
- `tests/` - PHPUnit and Pest test suites
- `.ddev/` - DDEV local development environment
- `.github/` - GitHub Actions CI/CD workflows

## Naming Conventions
- Models: PascalCase singular (`User`, `StatusHashtag`)
- Controllers: PascalCase with `Controller` suffix
- Database tables: snake_case plural (`users`, `status_hashtags`)
- Routes: kebab-case (`/admin/users`, `/api/v1/accounts`)
- Vue components: PascalCase (`ProfileCard.vue`)
- CSS classes: kebab-case following Bootstrap conventions