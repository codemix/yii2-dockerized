# Application base image
#
# This image contains:
#
#  - PHP runtime
#  - PHP extensions
#  - Composer packages


# Build stage 1: Install composer packages
FROM composer AS vendor
COPY composer.json /app
COPY composer.lock /app
RUN ["composer", "install", "--ignore-platform-reqs", "--prefer-dist"]


# Build stage 2: Final image
FROM alpine:3.9

# Add the S6 supervisor overlay
# https://github.com/just-containers/s6-overlay
RUN wget -O /tmp/s6-overlay-amd64.tar.gz \
        https://github.com/just-containers/s6-overlay/releases/download/v1.22.1.0/s6-overlay-amd64.tar.gz \
    && tar xzf /tmp/s6-overlay-amd64.tar.gz -C / \
    && rm /tmp/s6-overlay-amd64.tar.gz

# PHP 7.3 is not yet available from alpine packages so we use
# https://github.com/codecasts/php-alpine
RUN wget -O /etc/apk/keys/php-alpine.rsa.pub \
        https://dl.bintray.com/php-alpine/key/php-alpine.rsa.pub \
    && apk --update add ca-certificates \
    && echo "@php https://dl.bintray.com/php-alpine/v3.9/php-7.3" >> /etc/apk/repositories \
    && apk add --no-cache \
        #
        # Required packages
        nginx \
        php7@php \
        php7-ctype@php \
        php7-dom@php \
        #php7-fileinfo \
        php7-fpm@php \
        php7-intl@php \
        php7-json@php \
        php7-mbstring@php \
        php7-posix@php \
        php7-session@php \
        #php7-tokenizer \
        #
        # Optional extensions (modify as needed)
        php7-apcu@php \
        php7-opcache@php \
        php7-pdo_mysql@php \
    # Fix missing php binary
    && ln -s /usr/bin/php7 /usr/bin/php \
    #
    # Ensure user/group www-data for php-fpm
    && adduser -u 82 -D -S -G www-data www-data \
    #
    # Create pid dir and send logs to stderr for Nginx
    && mkdir /run/nginx \
    && ln -sf /dev/stderr /var/log/nginx/error.log \
    # Drop access logs as they only duplicate the host logs
    && ln -sf /dev/null /var/log/nginx/access.log \
    #
    # Set system timezone to make cron jobs run at correct local times
    && apk add --no-cache tzdata \
    && cp /usr/share/zoneinfo/Europe/Berlin /etc/localtime \
    && echo "Europe/Berlin" > /etc/timezone \
    && apk del --force-broken-world tzdata

# S6 configuration
ADD ./etc/cont-init.d /etc/cont-init.d
ADD ./etc/services.d /etc/services.d

# Nginx default server and PHP defaults
ADD ./etc/nginx/default.conf /etc/nginx/conf.d/default.conf
ADD ./etc/php7/zz-docker.conf /etc/php7/php-fpm.d/zz-docker.conf

# Composer packages from build stage 1
COPY --from=vendor /var/www/vendor /var/www/vendor

WORKDIR /var/www/html

EXPOSE 80

# S6 init will start all services
ENTRYPOINT ["/init"]
