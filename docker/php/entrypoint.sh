#!/bin/sh
set -e

cd /var/www/html/root

# Install PHP dependencies on first boot (vendor/ is gitignored). ext-http is
# declared in composer.json but never used, so the platform check is skipped.
if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] Installing Composer dependencies..."
    composer install --no-interaction --no-progress --ignore-platform-req=ext-http
fi

# Uploaded images live outside the web root and are not in git; make sure the
# directory exists and Apache can write to it.
mkdir -p /var/www/html/externalImages/categoryIcons
chown -R www-data:www-data /var/www/html/externalImages

exec apache2-foreground
