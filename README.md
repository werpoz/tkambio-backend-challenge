# TK Cambio Application

This project consists of a Laravel backend API and a Vue.js frontend application.

## Backend Setup

The backend is set up using Docker with Laravel Sail.

1. Navigate to the backend directory:
```bash
cd backend
```

2. Create environment file:
```bash
cp .env.example .env
```

3. Install Laravel Sail dependencies:
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

4. Start the Docker containers:
```bash
./vendor/bin/sail up -d
```

5. Generate application key:
```bash
./vendor/bin/sail artisan key:generate
```

6. Run database migrations and seeders:
```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed --class=AdminUserSeeder
./vendor/bin/sail artisan db:seed --class=UserSeeder
```

Default admin credentials:
- Email: admin@tkcambio.com
- Password: tkcambio

The backend API will be available at http://localhost

## Docker Requirements

Before starting with the Docker setup (Laravel Sail), ensure you have:

- Docker Engine installed and running on your system
- Docker Compose installed
- Minimum system requirements:
  - 2GB of RAM
  - 2 CPU cores

### Available Docker Services

The project includes the following services in the Docker configuration:

- PHP 8.4 with Laravel
- MySQL 8.0
- Redis
- Meilisearch
- Mailpit
- Selenium

## Additional Information

- The backend API will be available at: http://localhost
- The frontend application will be available at: http://localhost:5173
- Make sure Docker is installed and running on your system

## Queue Worker Setup

The application uses Laravel's queue system for processing background jobs (like report generation). There are two ways to run the queue worker:

### Method 1: Direct Command

1. Start the queue worker:
```bash
./vendor/bin/sail artisan queue:work
```

2. Monitor the queue (optional):
```bash
./vendor/bin/sail artisan queue:monitor
```

### Method 2: Supervisor (Recommended for Production)

1. Install Supervisor in the Docker container:
```bash
./vendor/bin/sail root-shell
apt-get update && apt-get install -y supervisor
exit
```

2. Create a configuration file for Laravel queue worker:
```bash
./vendor/bin/sail root-shell
cat > /etc/supervisor/conf.d/laravel-worker.conf << EOL
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=sail
numprocs=8
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
stopwaitsecs=3600
EOL
exit
```

3. Start Supervisor:
```bash
./vendor/bin/sail root-shell
supervisorctl reread
supervisorctl update
supervisorctl start laravel-worker:*
exit
```

4. Monitor Supervisor status:
```bash
./vendor/bin/sail root-shell
supervisorctl status
exit
```

Supervisor will automatically restart the queue worker if it fails and ensure it's always running. This is the recommended approach for production environments.

### Docker Commands Reference

```bash
# Start all containers
./vendor/bin/sail up -d

# Stop all containers
./vendor/bin/sail down

# View container logs
./vendor/bin/sail logs

# Run artisan commands
./vendor/bin/sail artisan [command]

# Run composer commands
./vendor/bin/sail composer [command]

# Run npm commands
./vendor/bin/sail npm [command]
```
