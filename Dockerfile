# Use official PHP 8.1 Apache image optimized for Render
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

# Configure PHP for production
RUN echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/custom.ini
RUN echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/custom.ini
RUN echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/custom.ini
RUN echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/custom.ini
RUN echo "display_errors = Off" >> /usr/local/etc/php/conf.d/custom.ini
RUN echo "log_errors = On" >> /usr/local/etc/php/conf.d/custom.ini

# Configure OPcache for performance
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini
RUN echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini
RUN echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini
RUN echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/opcache.ini
RUN echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/opcache.ini
RUN echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Copy application files (excluding .env files via .dockerignore)
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions for Render
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
RUN mkdir -p /var/www/html/uploads /var/www/html/cache
RUN chmod -R 777 /var/www/html/uploads /var/www/html/cache

# Configure Apache for production
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Create optimized Apache config
RUN cat > /etc/apache2/sites-available/000-default.conf << 'EOF'
<VirtualHost *:80>
    ServerAdmin admin@myfirstmovie.in
    DocumentRoot /var/www/html
    
    # Enable .htaccess overrides
    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set X-XSS-Protection "1; mode=block"
    
    # Remove server information
    Header always unset Server
    Header always unset X-Powered-By
    
    # Performance headers
    Header always set Cache-Control "public, max-age=31536000"
    Header always set Expires "access plus 1 year"
    
    # Error and access logs
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
    
    # PHP settings
    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>
    
    # Compress responses
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/plain
        AddOutputFilterByType DEFLATE text/html
        AddOutputFilterByType DEFLATE text/xml
        AddOutputFilterByType DEFLATE text/css
        AddOutputFilterByType DEFLATE application/xml
        AddOutputFilterByType DEFLATE application/xhtml+xml
        AddOutputFilterByType DEFLATE application/rss+xml
        AddOutputFilterByType DEFLATE application/javascript
        AddOutputFilterByType DEFLATE application/x-javascript
    </IfModule>
</VirtualHost>
EOF

# Expose port 80 (Render will handle HTTPS)
EXPOSE 80

# Health check endpoint
RUN echo "<?php http_response_code(200); echo 'OK'; ?>" > /var/www/html/health.php

# Start Apache with optimized configuration
CMD ["apache2-foreground"]
