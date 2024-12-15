FROM php:8.4.1-fpm

# Copy composer.lock and composer.json
COPY composer.lock composer.json /var/www/

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libxpm-dev \
    libfreetype6-dev \
    libzip-dev \
    locales \
    zip \
    unzip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    git \
    bash \
    curl \
    fcgiwrap \
    libmcrypt-dev \
    libonig-dev \
    libpq-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath opcache

# Install composer
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir/usr/local/bin --filename=composer
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# Add user for laravel application
RUN groupadd -g 1000 lordkarnak \
    && useradd -u 1000 -ms /bin/bash -g lordkarnak lordkarnak

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Change current user to www
USER lordkarnak

# Expopse port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
