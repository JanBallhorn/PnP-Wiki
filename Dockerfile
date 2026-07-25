FROM php:8.3-apache

# Runtime tooling + PHP extensions the app declares (mysqli, exif; fileinfo is
# bundled). git/unzip are for Composer package extraction.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip \
    && docker-php-ext-install mysqli exif \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Serve the app's web root (root/) and allow its .htaccess rewrites.
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Installs Composer deps on first boot and ensures the upload dir exists.
COPY docker/php/entrypoint.sh /usr/local/bin/wiki-entrypoint.sh
RUN chmod +x /usr/local/bin/wiki-entrypoint.sh

ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html/root

ENTRYPOINT ["/usr/local/bin/wiki-entrypoint.sh"]
