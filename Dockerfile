# Dockerfile
FROM php:8.2-cli

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port
EXPOSE 8000

# Start PHP server
CMD ["php", "-S", "0.0.0.0:8000", "index.php"]
