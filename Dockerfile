
FROM php:8.2-apache

# 1. Konfigurasi Apache MPM
RUN set -eux; \
    find /etc/apache2 -type f \( -name '*mpm*.load' -o -name '*mpm*.conf' \) -delete; \
    printf '%s\n' 'LoadModule mpm_prefork_module /usr/lib/apache2/modules/mod_mpm_prefork.so' > /etc/apache2/mods-available/mpm_prefork.load; \
    printf '%s\n' '# MPM prefork configuration' > /etc/apache2/mods-available/mpm_prefork.conf; \
    ln -sf ../mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load; \
    ln -sf ../mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

# 2. Periksa Apache
RUN set -eux; \
    apache2ctl -M 2>&1 | grep mpm; \
    apache2ctl configtest

# 3. Install MySQL
RUN docker-php-ext-install mysqli pdo_mysql

# 4. Aktifkan rewrite
RUN a2enmod rewrite

# 5. Server name
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 6. Salin website
COPY . /var/www/html/

# 7. Permission
RUN chown -R www-data:www-data /var/www/html

# 8. Port Railway
CMD ["sh", "-c", "set -eu; : \"${PORT:?PORT belum diberikan Railway}\"; sed -i 's/^Listen .*/Listen 0.0.0.0:'\"$PORT\"'/' /etc/apache2/ports.conf; sed -i 's/<VirtualHost \\*:80>/<VirtualHost *:'\"$PORT\"'>/' /etc/apache2/sites-available/000-default.conf; exec apache2-foreground"]
