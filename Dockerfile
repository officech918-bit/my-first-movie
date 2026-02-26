# Use official PHP 8.1 Apache image
FROM php:8.1-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    unzip \
    wget \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo_mysql \
    mysqli \
    zip \
    curl \
    xml \
    mbstring \
    opcache \
    bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache modules
RUN a2enmod rewrite
RUN a2enmod headers

# Configure PHP
RUN echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/custom.ini
RUN echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/custom.ini
RUN echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/custom.ini
RUN echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/custom.ini

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
RUN chmod -R 777 /var/www/html/uploads
RUN chmod -R 777 /var/www/html/cache

# Configure Apache
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Create Apache config for myfirstmovie1
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
