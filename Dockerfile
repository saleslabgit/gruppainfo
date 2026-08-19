FROM composer:2.8.12 AS composer

FROM php:8.2.32-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && docker-php-ext-install pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY docker/php/entrypoint.sh /usr/local/bin/project-entrypoint

RUN chmod +x /usr/local/bin/project-entrypoint

WORKDIR /var/www/html

ENTRYPOINT ["project-entrypoint"]
CMD ["php-fpm"]
