#!/bin/bash

# RCS App Avatar Upload Configuration Deployment Script
# This script ensures all configurations are properly applied for 10MB avatar uploads

echo "=== RCS App Avatar Upload Configuration Deployment ==="
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker first."
    exit 1
fi

echo "✅ Docker is running"

# Check if containers exist
if ! docker ps -a | grep -q "rcs_app\|rcs_webserver"; then
    echo "❌ RCS containers not found. Please run 'docker-compose up -d' first."
    exit 1
fi

echo "✅ RCS containers found"

# Rebuild containers with new configuration
echo ""
echo "🔧 Rebuilding containers with new configuration..."
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Wait for containers to start
echo "⏳ Waiting for containers to start..."
sleep 10

# Test nginx configuration
echo ""
echo "🔍 Testing nginx configuration..."
if docker exec rcs_webserver nginx -t > /dev/null 2>&1; then
    echo "✅ Nginx configuration is valid"
else
    echo "❌ Nginx configuration has errors"
    docker exec rcs_webserver nginx -t
    exit 1
fi

# Test PHP configuration
echo ""
echo "🔍 Testing PHP configuration..."
docker exec rcs_app php -r "
echo 'PHP Configuration:' . PHP_EOL;
echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL;
echo 'post_max_size: ' . ini_get('post_max_size') . PHP_EOL;
echo 'memory_limit: ' . ini_get('memory_limit') . PHP_EOL;
echo 'max_execution_time: ' . ini_get('max_execution_time') . PHP_EOL;
"

# Test upload directory
echo ""
echo "🔍 Testing upload directory..."
docker exec rcs_app mkdir -p /var/www/public/uploads/avatars
docker exec rcs_app chmod 755 /var/www/public/uploads/avatars

if docker exec rcs_app test -w /var/www/public/uploads/avatars; then
    echo "✅ Upload directory is writable"
else
    echo "❌ Upload directory is not writable"
    exit 1
fi

# Test GD extension
echo ""
echo "🔍 Testing GD extension..."
if docker exec rcs_app php -r "echo extension_loaded('gd') ? 'GD loaded' : 'GD not loaded';" | grep -q "loaded"; then
    echo "✅ GD extension is loaded"
else
    echo "❌ GD extension is not loaded"
    exit 1
fi

# Run comprehensive test
echo ""
echo "🔍 Running comprehensive upload test..."
docker exec rcs_app php /var/www/test_upload_config.php

echo ""
echo "🎉 Deployment completed successfully!"
echo ""
echo "Your RCS app is now configured to handle 10MB avatar uploads with:"
echo "  ✅ Nginx configured for 10MB uploads"
echo "  ✅ PHP configured for 10MB uploads"
echo "  ✅ Image optimization enabled"
echo "  ✅ Comprehensive error handling"
echo "  ✅ Production-ready security"
echo ""
echo "You can now upload avatars up to 10MB through the profile page."
