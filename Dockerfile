
#FROM php:8.2-apache
#FROM php:7.4-apache
FROM nibrev/php-5.3-apache

# Install the pdo_mysql extension
RUN docker-php-ext-install pdo_mysql

# Copy the application files to the container
COPY ./www /var/www/html

