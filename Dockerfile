ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-cli

# Install runtime dependencies required by this project.
RUN apt-get update \
    && apt-get install -y --no-install-recommends expat git unzip libcurl4-openssl-dev libxml2-dev \
    && docker-php-ext-install curl simplexml \
    && rm -rf /var/lib/apt/lists/*

# Reuse Composer binary from the official image.
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN git config --global --add safe.directory /workdir

WORKDIR /workdir

