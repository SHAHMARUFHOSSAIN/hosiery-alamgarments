FROM php:8.2-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libpng-dev libjpeg-dev libfreetype6-dev libzip-dev

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_pgsql zip

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project
COPY . .

RUN mkdir -p storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Permissions (important for Laravel)
RUN chmod -R 775 storage bootstrap/cache

# Install dependencies ONLY (NO artisan cache here)
RUN composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000