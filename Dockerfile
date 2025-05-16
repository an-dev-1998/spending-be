FROM php:8.3-fpm

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

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# ✅ Copy file .env từ .env.example
RUN cp .env.example .env

# ✅ Cài package
RUN composer install --no-dev --prefer-dist --no-interaction

# ✅ Tạo APP_KEY
RUN php artisan key:generate

# ✅ Phân quyền
RUN chown -R www-data:www-data storage bootstrap/cache

# ✅ Serve đúng thư mục public
EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
