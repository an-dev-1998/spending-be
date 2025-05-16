# 1. Image PHP chính với các extension cần thiết
FROM php:8.2-fpm

# 2. Cài đặt các package cần thiết
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nodejs \
    npm

# 3. Cài đặt PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# 4. Cài đặt Composer (quản lý package PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Cài NodeJS & Yarn (dùng để build frontend nếu cần)
RUN npm install -g yarn

# 6. Thiết lập thư mục làm việc
WORKDIR /var/www/html

# 7. Copy toàn bộ code vào container
COPY . .

# 8. Chạy composer install
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# 9. Chạy npm install & build frontend (nếu dùng yarn)
RUN yarn install
RUN yarn build

# 10. Tạo key Laravel (nếu bạn chưa tạo sẵn)
# RUN php artisan key:generate

# 11. Cấp quyền cho storage và cache (quan trọng)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 12. Mở port 9000 (port php-fpm)
EXPOSE 9000

# 13. Lệnh chạy PHP-FPM server khi container start
CMD ["php-fpm"]
