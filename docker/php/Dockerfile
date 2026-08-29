# ---------------------------------------------------------------------------
# Rahyaft Sanat — PHP 8.3 + Apache image for local development.
#
# Mirrors the production target described in README.md: Apache with
# mod_rewrite reading the project's own .htaccess, and the PHP extensions the
# installer checks for (pdo_mysql, mbstring, json, fileinfo required; gd,
# openssl, zip recommended).
# ---------------------------------------------------------------------------
FROM php:8.3-apache

# Build dependencies for gd/zip/intl, plus the MariaDB client the entrypoint
# uses to load database/schema.sql.
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev \
        libzip-dev libicu-dev \
        mariadb-client \
        ; \
    docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp; \
    docker-php-ext-install -j"$(nproc)" pdo_mysql gd zip intl exif opcache; \
    rm -rf /var/lib/apt/lists/*

# .htaccess needs rewrite; the security/caching blocks need headers, expires
# and deflate. Without these the <IfModule> guards silently skip them.
RUN a2enmod rewrite headers expires deflate; \
    printf 'ServerName localhost\n' > /etc/apache2/conf-available/servername.conf; \
    a2enconf servername

COPY docker/php/php.ini      /usr/local/etc/php/conf.d/zz-rahyaft.ini
COPY docker/php/vhost.conf   /etc/apache2/sites-available/000-default.conf
COPY docker/php/entrypoint.sh /usr/local/bin/rahyaft-entrypoint
RUN chmod +x /usr/local/bin/rahyaft-entrypoint

WORKDIR /var/www/html

ENTRYPOINT ["rahyaft-entrypoint"]
CMD ["apache2-foreground"]
