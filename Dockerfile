FROM php:8.3-cli-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
        bash \
        curl \
        git \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        unzip \
    && docker-php-ext-install \
        bcmath \
        intl \
        mbstring \
        pdo_mysql \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
ARG INSTALL_DEV=false
RUN if [ "$INSTALL_DEV" = "true" ]; then \
        composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts; \
    else \
        composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts; \
    fi

COPY . .
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

ENTRYPOINT ["entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
