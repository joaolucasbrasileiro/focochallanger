FROM php:8.4.25-apache-bookworm

COPY --from=composer:2.10.1 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install --yes --no-install-recommends cron git libzip-dev unzip util-linux \
    && docker-php-ext-install pdo_mysql zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install --no-interaction --no-progress --prefer-dist --no-scripts

COPY . .
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache/server-name.conf /etc/apache2/conf-available/server-name.conf
COPY docker/cron/entrypoint.sh /usr/local/bin/cron-entrypoint
COPY docker/cron/run-imports /usr/local/bin/run-imports

RUN composer dump-autoload --optimize \
    && a2enconf server-name \
    && php artisan l5-swagger:generate \
    && chmod 0755 /usr/local/bin/cron-entrypoint \
    && chmod 0755 /usr/local/bin/run-imports \
    && chown -R www-data:www-data bootstrap/cache storage
