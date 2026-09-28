FROM php:7.4-apache-bullseye

RUN printf '%s\n' \
    'deb http://archive.debian.org/debian bullseye main' \
    'deb http://archive.debian.org/debian bullseye-updates main' \
    > /etc/apt/sources.list \
    && apt-get -o Acquire::Check-Valid-Until=false update \
    && apt-get install -y --no-install-recommends libpng-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql gd \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

RUN printf '%s\n' \
    'upload_max_filesize = 110M' \
    'post_max_size = 110M' \
    'memory_limit = 256M' \
    'max_execution_time = 300' \
    'max_input_time = 300' \
    > /usr/local/etc/php/conf.d/uploads.ini

COPY apache.conf /etc/apache2/sites-available/000-default.conf

RUN chown -h www-data:www-data /var/www/html
