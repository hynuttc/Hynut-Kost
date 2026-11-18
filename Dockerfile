
FROM php:8.2-apache

# Bersihkan semua konfigurasi MPM yang aktif
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load \
    /etc/apache2/mods-enabled/mpm_*.conf

# Aktifkan hanya MPM Prefork
RUN a2enmod mpm_prefork

# Pastikan hanya satu MPM yang aktif
RUN apache2ctl -M | grep mpm

# Install ekstensi database
RUN docker-php-ext-install mysqli pdo_mysql

# Aktifkan rewrite
RUN a2enmod rewrite

# Salin project
COPY . /var/www/html/

# Jalankan Apache menggunakan port Railway
CMD ["sh", "-c", "sed -i \"s/Listen 80/Listen ${PORT}/\" /etc/apache2/ports.conf && sed -i \"s/:80>/:${PORT}>/\" /etc/apache2/sites-available/000-default.conf && exec apache2-foreground"]
