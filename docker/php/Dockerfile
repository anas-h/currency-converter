FROM php:8.3-fpm-alpine

# Extensions PHP nécessaires à Symfony et PostgreSQL
RUN apk add --no-cache icu-dev libzip-dev postgresql-dev $PHPIZE_DEPS \
    && docker-php-ext-install -j"$(nproc)" intl pdo_pgsql bcmath opcache zip \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /app
