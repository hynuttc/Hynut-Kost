FROM php:8.2-apache

# 1. Install ekstensi PHP untuk MySQL & kebutuhan web
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd

# 2. Hapus paksa semua konfigurasi MPM yang bentrok, lalu aktifkan mpm_prefork saja
RUN rm -f /etc/apache2/mods-enabled/mpm_* \
    && a2enmod mpm_prefork rewrite

# 3. Copy semua file project ke dalam server
COPY . /var/www/html/

# 4. (Khusus Laravel) Ubah DocumentRoot ke /public
# Hapus 3 baris di bawah ini jika website kamu adalah PHP Native biasa (bukan Laravel)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 5. Atur hak akses folder
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# 6. Jalankan Apache sesuai PORT dari Railway
CMD sed -i "s/80/${PORT:-80}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf && apache2-foreground