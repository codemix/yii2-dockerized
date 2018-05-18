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
FROM alpine:3.7

# Add the S6 overlay (https://github.com/just-containers/s6-overlay)
# S6 supervises the processes
RUN wget -O /tmp/s6-overlay-amd64.tar.gz \
        https://github.com/just-containers/s6-overlay/releases/download/v1.21.4.0/s6-overlay-amd64.tar.gz \
    && tar xzf /tmp/s6-overlay-amd64.tar.gz -C / \
    && rm /tmp/s6-overlay-amd64.tar.gz

RUN apk add --no-cache \
    # Required packages
    nginx \
    php7 \
    php7-ctype \
    php7-dom \
    php7-fileinfo \
    php7-fpm \
    php7-intl \
    php7-json \
    php7-mbstring \
    php7-posix \
    php7-session \
    php7-tokenizer \

    # Optional extensions (modify as needed)
    php7-apcu \
    php7-opcache \
    php7-pdo_mysql \

    # Ensure user/group www-data for php-fpm
    && adduser -u 82 -D -S -G www-data www-data \

    # Create pid dir and send logs to stderr for Nginx
    && mkdir /run/nginx \
    && ln -sf /dev/stderr /var/log/nginx/error.log \
    # Drop access logs as they only duplicate the host logs
    && ln -sf /dev/null /var/log/nginx/access.log \

    # Create Fifo for logging as php-fpm doesn't like writing to stdout/stderr
    && mkfifo /tmp/yii-stdout && chmod 666 /tmp/yii-stdout


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

# s6 init will start all services
ENTRYPOINT ["/init"]
