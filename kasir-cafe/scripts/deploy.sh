#!/usr/bin/env bash
set -euo pipefail

echo "== Deploy Kasir Cafe =="

if [ ! -f ".env" ]; then
  echo ".env not found. Copy from .env.example and fill values."
  exit 1
fi

php -v >/dev/null 2>&1 || { echo "PHP not found"; exit 1; }

if command -v composer >/dev/null 2>&1; then
  composer install --no-dev --optimize-autoloader
else
  echo "Composer not found"
  exit 1
fi

php artisan migrate --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Deploy complete."
