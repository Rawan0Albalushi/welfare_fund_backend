#!/bin/bash

# Student Welfare Fund API CI Script
# This script runs tests and checks for the Laravel API

set -e

echo "🚀 Starting CI pipeline for Student Welfare Fund API..."

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this script from the Laravel project root."
    exit 1
fi

# Install dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Install NPM dependencies
echo "📦 Installing NPM dependencies..."
npm install

# Copy environment file if it doesn't exist
if [ ! -f ".env" ]; then
    echo "📋 Copying environment file..."
    cp .env.example .env
fi

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate

# Clear caches
echo "🧹 Clearing application caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Run database seeders
echo "🌱 Running database seeders..."
php artisan db:seed --force

# Run tests
echo "🧪 Running tests..."
php artisan test

# Check code style (if Laravel Pint is available)
if command -v ./vendor/bin/pint &> /dev/null; then
    echo "🎨 Checking code style..."
    ./vendor/bin/pint --test
fi

# Security check (if available)
if command -v composer audit &> /dev/null; then
    echo "🔒 Running security audit..."
    composer audit
fi

# Build frontend assets (if needed)
echo "🏗️ Building frontend assets..."
npm run build

echo "✅ CI pipeline completed successfully!"
echo "🎉 The Student Welfare Fund API is ready for deployment!"

# Optional: Start the server for manual testing
if [ "$1" = "--serve" ]; then
    echo "🌐 Starting development server..."
    php artisan serve
fi
