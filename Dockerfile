FROM php:8.1-apache
# 1. Install development packages and clean up apt cache.
RUN apt-get update -y && apt-get install -y libwebp-dev libjpeg62-turbo-dev libpng-dev libxpm-dev \
  libfreetype6-dev zlib1g-dev  zip  unzip  libzip-dev supervisor

RUN rm -rf /var/lib/apt/lists/*
RUN echo "ServerName laravel-app.local" >> /etc/apache2/apache2.conf
COPY vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite headers
RUN pecl install -o -f redis 
RUN docker-php-ext-enable redis

RUN docker-php-ext-install mysqli pdo pdo_mysql sockets
RUN docker-php-ext-install zip
RUN docker-php-ext-install gd
RUN docker-php-ext-install exif
RUN docker-php-ext-install -j$(nproc) gd 
RUN docker-php-ext-install calendar

#RUN docker-php-ext-install php-redis
RUN echo 'memory_limit = -1' >> /usr/local/etc/php/conf.d/docker-php-ram-limit.ini

WORKDIR /var/www/html
ADD . /var/www/html

RUN chown -R www-data:www-data /var/www/html
RUN a2enmod rewrite
#-------------------------------------

# 5. Composer.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer update
#RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
#RUN composer update
EXPOSE 80