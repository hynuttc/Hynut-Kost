FROM php:8.2-apache

# 1. Install ekstensi PHP untuk MySQL
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd

# 2. Hapus modul MPM yang bentrok & aktifkan prefork
RUN rm -f /etc/apache2/mods-enabled/mpm_* \
    && a2enmod mpm_prefork rewrite

# 3. Copy semua file project ke web root Apache
COPY . /var/www/html/

# 4. Atur hak akses folder
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# 5. Jalankan Apache dengan port dinamis Railway
CMD sed -i "s/80/${PORT:-80}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf && apache2-foreground