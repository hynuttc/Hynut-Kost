FROM php:8.2-apache
RUN docker-php-ext-install mysqli pdo_mysql
RUN echo "Listen 0.0.0.0:\${PORT:-80}" > /etc/apache2/ports.conf && \
    echo "<VirtualHost *:\${PORT:-80}>" > /etc/apache2/sites-available/000-default.conf && \
    echo "    DocumentRoot /var/www/html" >> /etc/apache2/sites-available/000-default.conf && \
    echo "    ErrorLog \\${APACHE_LOG_DIR}/error.log" >> /etc/apache2/sites-available/000-default.conf && \
    echo "    CustomLog \\${APACHE_LOG_DIR}/access.log combined" >> /etc/apache2/sites-available/000-default.conf && \
    echo "</VirtualHost>" >> /etc/apache2/sites-available/000-default.conf
COPY . /var/www/html/