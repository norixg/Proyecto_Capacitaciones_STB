#!/usr/bin/env bash
set -e

# En desarrollo Docker se conserva una clave generada dentro de storage para
# que una instalación nueva funcione sin requerir un .env local.
if [ "${APP_ENV:-local}" = "production" ] && [ -z "${APP_KEY:-}" ]; then
    echo "ERROR: APP_KEY es obligatorio y debe ser persistente en producción." >&2
    exit 1
fi

if [ -z "${APP_KEY:-}" ]; then
    APP_KEY_FILE="storage/.docker_app_key"

    if [ ! -s "$APP_KEY_FILE" ]; then
        php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;" > "$APP_KEY_FILE"
    fi

    export APP_KEY="$(cat "$APP_KEY_FILE")"
fi

mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

if [ ! -L public/storage ]; then
    rm -rf public/storage
    php artisan storage:link || true
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${APP_ENV:-local}" = "production" ]; then
    php artisan optimize
else
    php artisan optimize:clear || true
fi

exec "$@"
