FROM php:8.2-fpm

WORKDIR /var/www

# system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# enable PHP extensions
RUN docker-php-ext-configure gd --with-freetype=/usr --with-jpeg=/usr \
    && docker-php-ext-install -j$(nproc) gd zip pdo pdo_pgsql

# composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# copy files
COPY . .

# setup env + permissions before composer
RUN cp .env.example .env \
    && mkdir -p storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# install dependencies (no config caching - Render injects env vars at runtime)
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan key:generate \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000
