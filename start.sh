#!/bin/bash

# Run migrations
php artisan migrate --force

# Start php-fpm and nginx
php-fpm &
nginx -g "daemon off;"
