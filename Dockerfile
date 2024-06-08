FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libssl-dev \
    libonig-dev \
    libxml2-dev \
    pkg-config \
    libcurl4-openssl-dev \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    zlib1g-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-install mongodb

# Copy custom mongodb.ini to the PHP configuration directory
#COPY ./mongodb.ini /usr/local/etc/php/conf.d/mongodb.ini

# Copy php_mongodb.dll to PHP extension directory
#COPY ./php_mongodb.dll /usr/lib/php/20230831/php_mongodb.dll

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Install Laravel dependencies
RUN composer install

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
