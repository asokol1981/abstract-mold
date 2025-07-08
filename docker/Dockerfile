ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-cli
RUN cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini
RUN apt-get update \
    && apt-get install -y unzip \
    && rm -rf /var/lib/apt/lists/*
RUN pecl install pcov
RUN docker-php-ext-enable pcov
COPY --from=composer:2.8.9 /usr/bin/composer /usr/bin/composer
ARG UID=1000
RUN adduser --uid $UID appuser
