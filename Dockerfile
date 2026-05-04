FROM php:8.2-fpm

WORKDIR /var/www

# system dependencies + gd deps
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# enable PHP extensions (IMPORTANT ORDER)
RUN docker-php-ext-configure gd --with-freetype=/usr --with-jpeg=/usr \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql

# composer install
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

# safety: ensure autoload works clean
RUN composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000