FROM php:8.2-apache

# System packages potrzebne dla GD/Composer
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install gd pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Composer z oficjalnego obrazu
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Ustaw katalog roboczy
WORKDIR /var/www/html

# Skopiuj projekt (na czas builda; w runtime i tak podmontujemy kod z hosta)
COPY . .

# Zależności PHP (jeśli jest composer.json -> install; jeśli nie -> doinstaluj dompdf)
RUN composer install --no-dev --prefer-dist --no-interaction || \
    composer require dompdf/dompdf:^2 --no-dev --prefer-dist --no-interaction

# Włącz mod_rewrite (na przyszłość)
RUN a2enmod rewrite
