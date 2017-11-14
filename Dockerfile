# Application image
#
# This image adds the application source to the bas image
#
FROM yii2-base-myapp:1.0

# Copy apache and PHP configuration for production into the image
COPY ./config/apache/productive.conf /etc/apache2/apache2.conf
COPY ./config/php/productive.ini /usr/local/etc/php/conf.d/productive.ini

# Copy the app code into the image
COPY . /var/www/html

# Create required directories listed in .dockerignore
RUN mkdir -p runtime web/assets var/session \
    && chown www-data:www-data runtime web/assets var/sessions
