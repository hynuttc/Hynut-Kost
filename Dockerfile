FROM php:8.2-apache
RUN docker-php-ext-install mysqli pdo_mysql
RUN echo "Listen 80" > /etc/apache2/ports.conf
COPY . /var/www/html/