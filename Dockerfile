FROM php:8.3-fpm

# Cài các dependency cần thiết
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    mbstring \
    gd \
    pcntl

# Cài Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy toàn bộ source code
COPY . .

# Cài đặt composer
RUN composer install --no-dev --prefer-dist --no-interaction

# Phân quyền cho storage và bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose port và run Laravel
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
