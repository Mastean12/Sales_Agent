FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        postgresql-dev \
        icu-dev \
        oniguruma-dev \
        libzip-dev \
        zip \
        git \
        supervisor \
    && docker-php-ext-install \
        pdo_pgsql \
        pcntl \
        bcmath \
        intl \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis

WORKDIR /var/www/html

COPY composer.json composer.lock ./
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
