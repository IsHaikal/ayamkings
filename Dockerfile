FROM php:8.2-apache

# Install PHP extensions for MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Fix Apache MPM conflict - disable event, enable prefork
RUN a2dismod mpm_event && a2enmod mpm_prefork

# Enable Apache mod_rewrite and headers
RUN a2enmod rewrite headers

# Set ServerName to suppress warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Change Apache to listen on port 8080 (Railway requirement)
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf
RUN sed -i 's/:80/:8080/g' /etc/apache2/sites-available/000-default.conf

# Copy backend files to Apache document root
COPY ./ayamkings_backend /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 755 /var/www/html/

# Expose port 8080 for Railway
EXPOSE 8080

CMD ["apache2-foreground"]
