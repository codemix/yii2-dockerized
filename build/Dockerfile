# The base image that provides the PHP runtime and composer packages
# which are requried by the application. You should use docker-compose
# to build/update the image

# Specific version constraint is recommended
FROM php:7.1.11-apache

# Set up PHP environment
RUN apt-get update \
    && apt-get -y install \
            g++ \
            libicu-dev \
        --no-install-recommends \

    # Enable mod_rewrite
    && a2enmod rewrite \

    # Extensions required by yii2
    && docker-php-ext-install intl \
    && docker-php-ext-install mbstring \

    # Optional extensions (modify as needed)
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install opcache \
    && pecl install apcu-5.1.8 \
        && echo extension=apcu.so > /usr/local/etc/php/conf.d/apcu.ini \

    # Cleanup to keep the images size small
    && apt-get purge -y g++ \
    && apt-get autoremove -y \
    && rm -r /var/lib/apt/lists/*

COPY vendor /var/www/vendor

WORKDIR /var/www/html
