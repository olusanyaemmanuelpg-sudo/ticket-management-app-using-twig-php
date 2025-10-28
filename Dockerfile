# Dockerfile
FROM php:8.2-cli

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set web root
WORKDIR /var/www/html

# Copy all files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 8000

# Serve from /var/www/html
CMD ["php", "-S", "0.0.0.0:8000", "-t", "/var/www/html"]