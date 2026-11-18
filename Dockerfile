
FROM php:8.2-apache

# =====================================
# 1. BERSIHKAN KONFIGURASI APACHE MPM
# =====================================
RUN set -eux; \
    find /etc/apache2/mods-enabled -maxdepth 1 \
    \( -name 'mpm_*.load' -o -name 'mpm_*.conf' \) \
    -delete; \
    a2enmod mpm_prefork; \
    apache2ctl -M 2>&1 | grep -q 'mpm_prefork_module'; \
    test "$(find /etc/apache2/mods-enabled -maxdepth 1 -name 'mpm_*.load' | wc -l)" -eq 1

# =====================================
# 2. INSTALL PHP MYSQL EXTENSIONS
# =====================================
RUN docker-php-ext-install mysqli pdo_mysql

# =====================================
# 3. AKTIFKAN APACHE REWRITE
# =====================================
RUN a2enmod rewrite

# Periksa seluruh konfigurasi MPM Apache
RUN echo "===== MPM LOAD FILES =====" && \
    find /etc/apache2 -type f \( -name "*.load" -o -name "*.conf" \) \
    -exec grep -HnE "mpm_(event|worker|prefork)" {} \; || true

RUN echo "===== ENABLED MPM MODULES =====" && \
    ls -la /etc/apache2/mods-enabled/mpm_*
    
# =====================================
# 4. KONFIGURASI APACHE
# =====================================
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# =====================================
# 5. SALIN FILE PROJECT
# =====================================
COPY . /var/www/html/

# =====================================
# 6. PERMISSION FOLDER
# =====================================
RUN chown -R www-data:www-data /var/www/html

# =====================================
# 7. KONFIGURASI PORT RAILWAY
# =====================================
CMD ["sh", "-c", "set -eu; : \"${PORT:?Railway PORT is not set}\"; sed -i 's/^Listen .*/Listen 0.0.0.0:'\"$PORT\"'/' /etc/apache2/ports.conf; sed -i 's/<VirtualHost \\*:80>/<VirtualHost *:'\"$PORT\"'>/' /etc/apache2/sites-available/000-default.conf; exec apache2-foreground"]
