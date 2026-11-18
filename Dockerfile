
FROM php:8.2-apache

# Install ekstensi database
RUN docker-php-ext-install mysqli pdo_mysql

# Aktifkan rewrite
RUN a2enmod rewrite

# Salin seluruh file project
COPY . /var/www/html/

# Atur port Railway dan jalankan Apache
CMD ["sh", "-c", "sed -i \"s/Listen 80/Listen ${PORT}/\" /etc/apache2/ports.conf && sed -i \"s/:80>/:${PORT}>/\" /etc/apache2/sites-available/000-default.conf && exec apache2-foreground"]
