# This is the Dockerfile to build the production image.
# It's not used for local development.

# Set this to the version of your base image
FROM myapp-base:1.0

# Copy apache and PHP configuration for production into the image
COPY ./config/apache/productive.conf /etc/apache2/apache2.conf
COPY ./config/php/productive.ini /usr/local/etc/php/conf.d/productive.ini

# Copy the app code into the image
COPY . /var/www/html

# The following directories are .dockerignored to not pollute the docker images
# with local logs and published assets from development. So we need to create
# empty dirs and set right permissions inside the container.
RUN mkdir runtime web/assets \
    && chown www-data:www-data runtime web/assets
