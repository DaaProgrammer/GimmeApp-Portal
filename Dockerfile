# PHP 8.2 with Apache
FROM php:8.2-apache

# Install system dependencies and enable PHP extensions
RUN apt-get update && apt-get install -y \
    libicu-dev \
    zlib1g-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    mariadb-client \
    curl \
    libcurl4-openssl-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure mysqli --with-mysqli=mysqlnd \
    && docker-php-ext-install pdo_mysql zip exif pcntl \
    && docker-php-ext-install gd \
    && docker-php-ext-install intl \
    && docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/* 

# Copy Apache configuration file
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Copy all files from the project root into the /var/www/html directory in the image
COPY . /var/www/html

# Run Composer update
WORKDIR /var/www/html

# Expose Apache
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
