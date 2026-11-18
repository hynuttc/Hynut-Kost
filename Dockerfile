FROM php:8.2-apache
RUN docker-php-ext-install mysqli pdo_mysql
RUN echo "Listen 80" > /etc/apache2/ports.conf
RUN sed -i 's/:80/:80/g' /etc/apache2/sites-available/000-default.conf
COPY . /var/www/html/