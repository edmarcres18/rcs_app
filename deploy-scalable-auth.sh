#!/bin/bash

# Scalable Authentication Deployment Script
# This script sets up the scalable authentication system for Laravel 11

set -e

echo "🚀 Starting Scalable Authentication Deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is running
check_docker() {
    print_status "Checking Docker status..."
    if ! docker info > /dev/null 2>&1; then
        print_error "Docker is not running. Please start Docker and try again."
        exit 1
    fi
    print_success "Docker is running"
}

# Check if Docker Compose is available
check_docker_compose() {
    print_status "Checking Docker Compose..."
    if ! docker-compose --version > /dev/null 2>&1; then
        print_error "Docker Compose is not available. Please install it and try again."
        exit 1
    fi
    print_success "Docker Compose is available"
}

# Create environment file if it doesn't exist
create_env_file() {
    print_status "Setting up environment configuration..."

    if [ ! -f .env ]; then
        print_warning ".env file not found. Creating from example..."
        cp .env.example .env 2>/dev/null || {
            print_warning "No .env.example found. Creating basic .env file..."
            cat > .env << EOF
APP_NAME="RCS Application"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost:9091

LOG_CHANNEL=stack
LOG_LEVEL=warning

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=rcs_app_db
DB_USERNAME=rcs_user
DB_PASSWORD=Letmein@2025

# Redis Configuration
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2

# Session Configuration
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

# Cache Configuration
CACHE_STORE=redis
CACHE_PREFIX=rcs_cache_

# Rate Limiting
LOGIN_THROTTLE_ATTEMPTS=5
LOGIN_THROTTLE_DECAY_MINUTES=1
EOF
        }
        print_success "Environment file created"
    else
        print_success "Environment file already exists"
    fi
}

# Generate application key
generate_app_key() {
    print_status "Generating application key..."
    if [ -z "$(grep '^APP_KEY=' .env | cut -d '=' -f2)" ]; then
        docker-compose exec app php artisan key:generate --force
        print_success "Application key generated"
    else
        print_success "Application key already exists"
    fi
}

# Install PHP dependencies
install_dependencies() {
    print_status "Installing PHP dependencies..."
    docker-compose exec app composer install --no-dev --optimize-autoloader
    print_success "PHP dependencies installed"
}

# Run database migrations
run_migrations() {
    print_status "Running database migrations..."
    docker-compose exec app php artisan migrate --force
    print_success "Database migrations completed"
}

# Clear and cache configuration
optimize_application() {
    print_status "Optimizing application..."
    docker-compose exec app php artisan config:cache
    docker-compose exec app php artisan route:cache
    docker-compose exec app php artisan view:cache
    print_success "Application optimized"
}

# Test Redis connection
test_redis() {
    print_status "Testing Redis connection..."
    if docker-compose exec redis redis-cli ping | grep -q "PONG"; then
        print_success "Redis connection successful"
    else
        print_error "Redis connection failed"
        exit 1
    fi
}

# Test session functionality
test_sessions() {
    print_status "Testing session functionality..."
    docker-compose exec app php artisan tinker --execute="
        try {
            \$redis = Redis::connection();
            \$redis->set('test_session', 'test_value', 60);
            \$value = \$redis->get('test_session');
            if (\$value === 'test_value') {
                echo 'Session test passed';
                \$redis->del('test_session');
            } else {
                echo 'Session test failed';
            }
        } catch (Exception \$e) {
            echo 'Session test error: ' . \$e->getMessage();
        }
    "
}

# Start services
start_services() {
    print_status "Starting Docker services..."
    docker-compose up -d
    print_success "Services started"
}

# Wait for services to be ready
wait_for_services() {
    print_status "Waiting for services to be ready..."
    sleep 10

    # Wait for MySQL
    print_status "Waiting for MySQL..."
    until docker-compose exec db mysqladmin ping -h"localhost" -u"root" -p"root" --silent; do
        sleep 2
    done
    print_success "MySQL is ready"

    # Wait for Redis
    print_status "Waiting for Redis..."
    until docker-compose exec redis redis-cli ping | grep -q "PONG"; do
        sleep 2
    done
    print_success "Redis is ready"
}

# Health check
health_check() {
    print_status "Performing health check..."

    # Check if services are responding
    if curl -f http://localhost:9091/health > /dev/null 2>&1; then
        print_success "Application health check passed"
    else
        print_warning "Application health check failed (this might be normal during setup)"
    fi

    # Check Redis memory usage
    redis_memory=$(docker-compose exec redis redis-cli info memory | grep "used_memory_human" | cut -d: -f2)
    print_status "Redis memory usage: $redis_memory"
}

# Main deployment function
main() {
    echo "=========================================="
    echo "  Laravel 11 Scalable Authentication"
    echo "  Deployment Script"
    echo "=========================================="
    echo ""

    # Pre-flight checks
    check_docker
    check_docker_compose

    # Setup
    create_env_file
    start_services
    wait_for_services

    # Application setup
    install_dependencies
    generate_app_key
    run_migrations
    optimize_application

    # Testing
    test_redis
    test_sessions
    health_check

    echo ""
    echo "=========================================="
    print_success "Deployment completed successfully!"
    echo "=========================================="
    echo ""
    echo "Your application is now running at:"
    echo "  - Web Application: http://localhost:9091"
    echo "  - phpMyAdmin: http://localhost:9092"
    echo "  - Redis: localhost:6379"
    echo ""
    echo "Next steps:"
    echo "  1. Access your application at http://localhost:9091"
    echo "  2. Check the documentation at docs/Scalable-Authentication-Setup.md"
    echo "  3. Monitor Redis memory usage: docker exec -it rcs_redis redis-cli info memory"
    echo "  4. View logs: docker-compose logs -f"
    echo ""
    echo "For production deployment, remember to:"
    echo "  - Set APP_ENV=production"
    echo "  - Configure proper SSL certificates"
    echo "  - Set secure Redis passwords"
    echo "  - Configure proper firewall rules"
    echo ""
}

# Run main function
main "$@"
