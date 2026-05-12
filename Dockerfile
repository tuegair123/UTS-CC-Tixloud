FROM php:8.2-fpm

# Install system dependencies (menambahkan libicu-dev)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (menambahkan konfigurasi dan instalasi intl)
RUN docker-php-ext-configure intl
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd intl

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

USER www-data