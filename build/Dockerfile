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
FROM alpine:3.11

# Add the S6 supervisor overlay
# https://github.com/just-containers/s6-overlay
RUN wget -O /tmp/s6-overlay-amd64.tar.gz \
        https://github.com/just-containers/s6-overlay/releases/download/v1.22.1.0/s6-overlay-amd64.tar.gz \
    && tar xzf /tmp/s6-overlay-amd64.tar.gz -C / \
    && rm /tmp/s6-overlay-amd64.tar.gz

RUN apk add --no-cache \
        #
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
        #
        # Optional extensions (modify as needed)
        php7-apcu \
        php7-opcache \
        php7-pdo_mysql \
    #
    # Ensure user/group www-data for php-fpm
    && adduser -u 82 -D -S -G www-data www-data \
    #
    # Nginx: Create pid dir, send error logs to stderr and drop
    # access logs as they only duplicate the host Nginx logs
    && mkdir /run/nginx \
    && ln -sf /dev/stderr /var/log/nginx/error.log \
    && ln -sf /dev/null /var/log/nginx/access.log \
    #
    # Create FIFOs for stderr/stdout of yii console and other processes.
    # Two s6 services then pipe the content to stdout/stderr.
    && mkfifo /docker-stdout && chmod 666 /docker-stdout \
    && mkfifo /docker-stderr && chmod 666 /docker-stderr \
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
