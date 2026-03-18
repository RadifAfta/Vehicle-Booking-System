#!/usr/bin/env sh
set -e

cd /var/www

if [ -f .env.example ] && [ ! -f .env ]; then
  cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force >/dev/null 2>&1 || true
fi

if [ -n "$DB_HOST" ]; then
  echo "Waiting for database at ${DB_HOST}:${DB_PORT:-3306}..."
  until mysqladmin ping -h"$DB_HOST" -P"${DB_PORT:-3306}" --silent; do
    sleep 2
  done
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force
  php artisan db:seed --force  # <-- Tambahkan baris ini
fi

exec "$@"
