# Image commune aux 3 microservices et au frontend Laravel.
# Le squelette Laravel 11 est créé au build, puis on y superpose le code de chaque service (overlay).
FROM php:8.3-cli
RUN apt-get update && apt-get install -y --no-install-recommends git unzip curl ca-certificates netcat-openbsd \
 && curl -fsSL https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions -o /usr/local/bin/install-php-extensions \
 && chmod +x /usr/local/bin/install-php-extensions \
 && install-php-extensions pdo_pgsql pdo_sqlite zip bcmath \
 && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/app
ENV COMPOSER_ALLOW_SUPERUSER=1
# Composer 2.9+ bloque les paquets avec advisory de sécurité ; Laravel 11 est
# flagué sur toute sa branche. Le blocage est désactivé le temps du build.
ENV COMPOSER_NO_BLOCKING=1
RUN composer create-project laravel/laravel:^11.0 . --no-scripts --no-interaction --prefer-dist \
 && cp .env.example .env \
 && rm -f app/Models/User.php database/migrations/*.php database/factories/*.php database/seeders/*.php \
 && composer require firebase/php-jwt:^6.10 --no-interaction

ARG SERVICE_DIR
ARG EXTRA_PACKAGES=""
# WITH_DEV=0 (production) : purge les dépendances require-dev (phpunit…) de l'image
ARG WITH_DEV=1
RUN if [ -n "$EXTRA_PACKAGES" ]; then composer require $EXTRA_PACKAGES --no-interaction; fi
COPY services/_shared/overlay/ ./
COPY ${SERVICE_DIR}/overlay/ ./
COPY services/_shared/docker-entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint \
 && if [ "$WITH_DEV" = "0" ]; then composer install --no-interaction --prefer-dist --no-dev; fi \
 && composer dump-autoload -o --no-scripts && php artisan package:discover --ansi && rm -f .env
EXPOSE 8000
ENTRYPOINT ["entrypoint"]
