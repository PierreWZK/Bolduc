# syntax=docker/dockerfile:1
# ---------------------------------------------------------------------------
# Bolduc en production : FrankenPHP (Caddy + PHP 8.4) derrière le proxy de Coolify.
#
# public/build n'est pas versionné : les assets Vite sont compilés dans une première étape.
# Après chaque déploiement, Coolify lance (voir README, « Mise en ligne ») :
#   php artisan migrate --force && php artisan db:seed --class=IdeeSeeder --force
#   && php artisan db:seed --class=DailyItemSeeder --force && php artisan optimize
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

FROM dunglas/frankenphp:1-php8.4

WORKDIR /app

# Extensions PHP : MySQL, formats et dates en français, cache d'opcodes, archives pour Composer
RUN install-php-extensions pdo_mysql intl opcache zip \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-bolduc.ini"
COPY docker/Caddyfile /etc/caddy/Caddyfile

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Dépendances d'abord : cette couche reste en cache tant que composer.lock ne change pas
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress --no-interaction

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --no-dev --optimize \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

EXPOSE 80
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
