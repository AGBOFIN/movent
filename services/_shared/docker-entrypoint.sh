#!/bin/sh
set -e
cd /var/www/app
# Clé d'application : générée si absente (aucune session/chiffrement persistant n'est utilisé)
case "$APP_KEY" in base64:*) ;; *) export APP_KEY="$(php artisan key:generate --show)";; esac
if [ -n "$DB_HOST" ]; then
  echo "Attente de PostgreSQL ($DB_HOST:${DB_PORT:-5432})..."
  until nc -z "$DB_HOST" "${DB_PORT:-5432}"; do sleep 1; done
  php artisan migrate --force
  php artisan db:seed --force
fi
exec php artisan serve --host=0.0.0.0 --port=8000 --no-reload
