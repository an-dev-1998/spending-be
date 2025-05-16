# Dockerfile
FROM php:8.3-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    unzip \
    zip \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip mbstring gd

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy app files
COPY . .

# Copy nginx config
COPY nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy start script
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Set permissions
RUN chmod -R 775 storage bootstrap/cache

# Install Laravel dependencies
RUN composer install --no-dev --prefer-dist --no-interaction

# Clear and cache config after build
RUN php artisan config:clear && \
    php artisan config:cache && \
    php artisan route:cache

EXPOSE 80

CMD ["/start.sh"]
