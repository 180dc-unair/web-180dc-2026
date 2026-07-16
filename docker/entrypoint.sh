#!/bin/sh
set -e

echo "==> Setting up storage directories..."
mkdir -p \
    storage/framework/views \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/app/public \
    storage/logs \
    bootstrap/cache

chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "==> Checking .env file..."
if [ ! -f .env ]; then
    echo "    .env not found, copying from .env.example..."
    cp .env.example .env
    php artisan key:generate --ansi
else
    echo "    .env found, skipping..."
fi

echo "==> Running composer install..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "==> Running artisan commands..."
php artisan config:clear
php artisan view:clear
php artisan storage:link --force || true

echo "==> Starting php-fpm..."
exec php-fpm