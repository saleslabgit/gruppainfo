#!/bin/sh
set -eu

export COMPOSER_CACHE_DIR=/tmp/composer-cache

if [ ! -f .env ]; then
    cp .env.example .env
fi

composer install --no-interaction --prefer-dist

if ! grep -Eq '^APP_KEY=base64:.+' .env; then
    php artisan key:generate --force --no-interaction
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R u+rwX,g+rwX storage bootstrap/cache

php artisan migrate --force --no-interaction

exec "$@"
