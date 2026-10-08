#!/bin/sh
set -e
# Render provides $PORT; default to 80 elsewhere.
PORT="${PORT:-80}"
sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize || true
php artisan migrate --force
if [ "${RUN_SEED_ROLES:-true}" = "true" ]; then php artisan db:seed --class=RoleSeeder --force; php artisan db:seed --class=PermissionSeeder --force; fi
exec apache2-foreground
