# Dockerfile
FROM php:8.2-cli

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory to web root
WORKDIR /var/www/html

# Copy ALL files to web root
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 8000

# Start PHP server from web root
CMD ["php", "-S", "0.0.0.0:8000", "-t", "/var/www/html", "/var/www/html/index.php"]