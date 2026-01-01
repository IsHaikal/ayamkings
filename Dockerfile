FROM php:8.2-apache

# Install PHP extensions for MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite and headers
RUN a2enmod rewrite headers

# Set ServerName to suppress warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy backend files to Apache document root
COPY ./ayamkings_backend /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 755 /var/www/html/

# Expose port 80
EXPOSE 80

CMD ["apache2-foreground"]
