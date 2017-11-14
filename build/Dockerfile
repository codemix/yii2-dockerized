# Application base image
#
# This image contains:
#
#  - PHP runtime
#  - PHP extensions
#  - Composer packages


# Build stage to install composer packages
FROM composer AS vendor
COPY composer.json /app
COPY composer.lock /app
RUN ["composer", "install"]


# Build stage of the final image
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

    # Optional extensions (modify as needed)
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install opcache \
    && pecl install apcu-5.1.8 \
        && echo extension=apcu.so > /usr/local/etc/php/conf.d/apcu.ini \

    # Cleanup to keep the images size small
    && apt-get purge -y g++ \
    && apt-get autoremove -y \
    && rm -r /var/lib/apt/lists/*

COPY --from=vendor /var/www/vendor /var/www/vendor

WORKDIR /var/www/html
