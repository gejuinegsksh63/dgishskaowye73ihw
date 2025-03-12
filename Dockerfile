# Use an official PHP-Apache image
FROM php:apache

# Copy all project files to the container
COPY . /var/www/html/

# Expose the default Apache port
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
