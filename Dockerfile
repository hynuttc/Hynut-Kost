
FROM php:8.2-apache

# Pastikan hanya MPM prefork yang aktif
RUN a2dismod mpm_event mpm_worker || true
RUN a2enmod mpm_prefork

# Install ekstensi database
RUN docker-php-ext-install mysqli pdo_mysql

# Salin file project
COPY . /var/www/html/

# Atur port Railway saat container berjalan
CMD ["sh", "-c", "sed -i 's/Listen 80/Listen ${PORT:-80}/' /etc/apache2/ports.conf && sed -i 's/:80>/:${PORT:-80}>/' /etc/apache2/sites-available/000-default.conf && exec apache2-foreground"]
