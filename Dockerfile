
FROM php:8.2-apache

# 1. BERSIHKAN MODUL MPM
RUN set -eux; \
    a2dismod mpm_event mpm_worker mpm_prefork || true; \
    rm -f /etc/apache2/mods-enabled/mpm_*.load; \
    rm -f /etc/apache2/mods-enabled/mpm_*.conf; \
    a2enmod mpm_prefork; \
    echo "===== ACTIVE MPM MODULES ====="; \
    ls -la /etc/apache2/mods-enabled/ | grep mpm; \
    test "$(find /etc/apache2/mods-enabled -maxdepth 1 -name 'mpm_*.load' | wc -l)" -eq 1; \
    apache2ctl -M 2>&1 | grep mpm; \
    apache2ctl configtest

# 2. INSTALL PHP MYSQL
RUN docker-php-ext-install mysqli pdo_mysql

# 3. AKTIFKAN REWRITE
RUN a2enmod rewrite

# 4. SERVER NAME
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 5. SALIN FILE WEBSITE
COPY . /var/www/html/

# 6. ATUR PERMISSION
RUN chown -R www-data:www-data /var/www/html

# 7. JALANKAN APACHE PADA PORT RAILWAY
CMD ["sh", "-c", "set -eu; : \"${PORT:?PORT belum diberikan Railway}\"; sed -i 's/^Listen .*/Listen 0.0.0.0:'\"$PORT\"'/' /etc/apache2/ports.conf; sed -i 's/<VirtualHost \\*:80>/<VirtualHost *:'\"$PORT\"'>/' /etc/apache2/sites-available/000-default.conf; exec apache2-foreground"]
