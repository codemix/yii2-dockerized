FROM php:5.6-apache

WORKDIR /var/www/html

# Install required packages and PHP modules
RUN apt-get update \
    && apt-get -y install \
            git \
            libmcrypt-dev \
            zlib1g-dev \
        --no-install-recommends \
    && rm -r /var/lib/apt/lists/* \

    # Install PHP extensions
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install mcrypt \
    && docker-php-ext-install zip \
    && pecl install apcu-beta && echo extension=apcu.so > /usr/local/etc/php/conf.d/apcu.ini \

    # Install composer
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \

    && composer global require "fxp/composer-asset-plugin:1.0.0" \

    && a2enmod rewrite \

    # Update apache2.conf
    && sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/web#' /etc/apache2/apache2.conf

# We first install any composer packages outside of the web root to prevent them
# from being overwritten by the COPY below. If the composer.lock file here didn't
# change, docker will use the cached composer files.
COPY composer.json /var/www/html/
COPY composer.lock /var/www/html/
RUN composer self-update && \
    composer install --no-progress

# Finally copy the working dir to the image's web root
COPY . /var/www/html

# The following directories are .dockerignored to not pollute the docker images
# with local logs and published assets from development. So we need to create
# empty dirs and set right permissions inside the container.
RUN mkdir runtime web/assets \
    && chown www-data:www-data runtime web/assets

