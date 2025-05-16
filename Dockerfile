FROM php:8.3-cli

# Cài đặt các dependency cần thiết
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

# Cài đặt Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www

# Sao chép mã nguồn vào container
COPY . .

# Cài đặt các package PHP
RUN composer install --no-dev --prefer-dist --no-interaction

# Phân quyền cho thư mục storage và bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Generate application key if not set
RUN php artisan key:generate --no-interaction

# Clear and cache configuration
RUN php artisan config:clear \
    && php artisan config:cache \
    && php artisan route:clear \
    && php artisan route:cache \
    && php artisan view:clear \
    && php artisan view:cache

# Mở cổng 8000
EXPOSE 8000

# Chạy ứng dụng Laravel sử dụng artisan serve
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
