FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
        mc \
        curl \
        wget \
        git \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libonig-dev \
        libzip-dev \
        libmcrypt-dev \
        && pecl install mcrypt-1.0.3 \
        && docker-php-ext-enable mcrypt \
        && docker-php-ext-install -j$(nproc) iconv mbstring mysqli pdo_mysql zip \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install -j$(nproc) gd

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ADD php.ini /usr/local/etc/php/conf.d/40-custom.ini

WORKDIR /var/www

CMD ["sh", "-c", "composer install --optimize-autoloader --working-dir=/var/www/url-shortener.loc && php /var/www/url-shortener.loc/bin/console doctrine:migrations:migrate --no-interaction && php-fpm"]
