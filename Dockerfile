# syntax=docker/dockerfile:1.7
FROM php:8.4-fpm-alpine AS php-base

RUN apk add --no-cache icu-libs libzip libpng oniguruma \
    && apk add --no-cache --virtual .build-deps icu-dev libzip-dev libpng-dev oniguruma-dev $PHPIZE_DEPS \
    && docker-php-ext-install -j$(nproc) bcmath intl opcache pdo_mysql zip \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html

FROM php-base AS vendor
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --optimize-autoloader

FROM php-base AS assets
RUN apk add --no-cache nodejs npm
COPY . .
COPY --from=vendor /var/www/html/vendor ./vendor
RUN npm ci && npm run build:ssr

FROM php-base AS app
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr
COPY . .
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=assets /var/www/html/public/build ./public/build
COPY --from=assets /var/www/html/bootstrap/ssr ./bootstrap/ssr
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
USER www-data
EXPOSE 9000
CMD ["php-fpm", "-F"]

FROM assets AS ssr
ENV APP_ENV=production APP_DEBUG=false
CMD ["php", "artisan", "inertia:start-ssr"]
