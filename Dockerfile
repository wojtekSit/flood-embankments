FROM php:8.2-apache

# Włącz rozszerzenia PHP wymagane przez projekt
RUN docker-php-ext-install pdo pdo_mysql

# Skopiuj pliki projektu do kontenera
COPY . /var/www/html/

# Ustaw katalog roboczy
WORKDIR /var/www/html/

# Włącz mod_rewrite (przydatne w przyszłości)
RUN a2enmod rewrite
