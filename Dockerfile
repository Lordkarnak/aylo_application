FROM php:8.4.1-fpm

USER root

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
    npm \
    jpegoptim optipng pngquant gifsicle \
    vim \
    git \
    bash \
    curl \
    fcgiwrap \
    libmcrypt-dev \
    libonig-dev \
    libpq-dev \
    sudo \
    libuser \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath opcache

# Install composer
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir/usr/local/bin --filename=composer
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# Add user for laravel application
RUN if [ ! $(getent group lordkarnak) ]; then lgroupadd -g 1000 lordkarnak; fi \
    && if [ ! id lordkarnak ]; then luseradd -u 1000 -ms /bin/bash -g lordkarnak lordkarnak; fi

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Create the log file to be able to run tail
RUN touch /var/log/cron.log      

# Apply file permissions to the cron job
RUN chmod 0666 /var/log/cron.log  

# Late update, install cron because I forgot to do it in the large package
RUN apt-get update && apt-get install -y cron

# Add the cron job
RUN { crontab -l -u lordkarnak; echo "* * * * * /usr/bin/php /var/www/html/artisan schedule:run >> /var/log/cron.log 2>&1"; } | crontab - 

RUN npm create vite@latest

# Change current user to www
USER lordkarnak

# Expopse port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
