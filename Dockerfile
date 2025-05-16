# Stage 1: Build composer dependencies
FROM composer:2.6 AS composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction

# Stage 2: PHP App Server
FROM php:8.3-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl

# Install Composer (copied from build stage)
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . .

# Copy vendor from builder
COPY --from=composer /app/vendor ./vendor

# Laravel permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose port (change if needed)
EXPOSE 8000

# Set entrypoint (you can change serve to use nginx/apache if needed)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
