#!/bin/sh
set -e
# Render provides $PORT; default to 80 elsewhere.
PORT="${PORT:-80}"

if [ -z "$APP_KEY" ]; then echo "APP_KEY is not set (php artisan key:generate --show)" >&2; exit 1; fi

sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
APP_URL=$(printf %s "$APP_URL" | tr -d '[:space:]"'"'"); export APP_URL
case "$APP_URL" in
  http://*|https://*) ;;
  *) if [ -n "$RENDER_EXTERNAL_URL" ]; then
       echo "APP_URL invalid ('$APP_URL'); using RENDER_EXTERNAL_URL" >&2
       export APP_URL="$RENDER_EXTERNAL_URL"
     else
       echo "APP_URL must start with https:// (got: '$APP_URL'), fix it in Render > Environment" >&2; exit 1
     fi ;;
esac

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize || true
php artisan migrate --force
if [ "${RUN_SEED_ROLES:-true}" = "true" ]; then php artisan db:seed --class=RoleSeeder --force; php artisan db:seed --class=PermissionSeeder --force; fi
# Create/refresh the first admin from env vars (Render dashboard).
if [ -n "$ADMIN_EMAIL" ] && [ -n "$ADMIN_PASSWORD" ]; then php artisan db:seed --class=AdminSeeder --force; fi
exec apache2-foreground
