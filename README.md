Yii 2 Dockerized
================

A template for Yii 2 applications based on the
[codemix/yii2-base](https://registry.hub.docker.com/u/codemix/yii2-base/) docker image.

 * Ephemeral container which gets configured via environment variables
 * Optional local configuration overrides for development/debugging (git-ignored)
 * Base scaffold code for login, signup and forgot-password actions
 * Flat configuration file structure
 * Support docker image based development workflow

The `yii2-base` image comes in three flavours:

 * **Apache with PHP module** (based on `php:5.6.6-apache`)
 * **PHP-FPM** (based on `php:5.6.6-fpm`)
 * **HHVM** (based on `estebanmatias92/hhvm:3.5.1-fastcgi`)

1. Quickstart
-------------

You need to have [docker](http://www.docker.com) (>=1.5.0) and
[docker-compose](https://docs.docker.com/compose/install/) installed.
Then you can quickly start a new app with

```sh
composer create-project --no-install codemix/yii2-dockerized myproject
cp docker-compose-example.yml docker-compose.yml
docker-compose up
# From another terminal window:
docker-compose run --rm web ./yii migrate
```

This may take some minutes to download the required docker images. When
done, you can access the new app from [http://localhost:8080](http://localost:8080).

> *Note:* If you're ony Windows or Mac OS you have to use [boot2docker](https://github.com/boot2docker/boot2docker)
> instead. There's also a [workaround](https://github.com/artsemis/docker-docker-compose)
> to make `docker-compose` available.

2. How To Use
-------------

This project is a template for your own Yii2 applications. You can use it as a starting
point and modify it to fit your requirements. It's also kind of an alternative to the official
yii2 base and advanced apps.


### 2.1 Starting A New Project

As shown in the quickstart you will first create a new project with `composer` or, if you don't
have composer installed, [download](https://github.com/codemix/yii2-dockerized/releases)
and uncompress the files into a new directory.

Before you commit the app into *your* repository, you first will want to set up the project
for your purpose.

The first step is to select the `yii2-base` image flavour in the `Dockerfile`. So uncomment
the apropriate line:

```
FROM codemix/yii2-base:2.0.3-apache
#FROM codemix/yii2-base:2.0.3-php-fpm
#FROM codemix/yii2-base:2.0.3-hhvm
```

Then you should check the following files and directories:

 * `Dockerfile`:  Select the base image (Apache + mod_php or php-fpm + nginx) and add PHP extensions
 * `docker-compose-example.yml`: Provide an example machine configuration for other developers
 * `config/`: Update the default configuration
 * `.env-example`: Add/remove environment variables
 * `composer.json`: Add/remove dependencies for your project
 * `migrations/`: Update the initial DB migration to match your DB model
 * `README.md`: Describe *your* application

When you're done, you will commit the changes to your repository to make it available
for other developers in your team.


### 2.2 Setting Up The Development Environment

When you have the project in your repository, it's easy to set up a new development environment,
e.g. for a new team member:

```sh
git clone <your-repo-url> ./myapp
cd myapp
cp docker-compose-example.yml docker-compose.yml
cp .env-example .env
docker-compose up
# From another terminal window:
docker-compose run --rm web ./yii migrate
```

Now you can work on the source files locally and immediately see the results under the URL
of the container. This is made possible, because in the default `docker-compose.yml` we
map the local project directory into the `/var/www/html` directory of the container.


### 2.3 Environment Variables

This template follows the [12 factor](http://12factor.net/) principles. This means, that
all dynamic configuration parameters like for example database credentials or secret
API keys are passed to the container through **environment variables**.

For local development you could configure those variables in the `environment` section of your
`docker-compose.yml`. But this requires to rebuild the container each time you change a variable.
That's why we use [phpdotenv](https://github.com/vlucas/phpdotenv) in this project, to keep
this configuration in a local `.env` file. Changes there are picked up immediately.

In production you will use whatever means are neccessary to set the environment variables.
This depends on how you host your docker image (or if you use docker in production at all).

The template uses the following configuration variables:

 * `ENABLE_ENV_FILE` whether to load env vars from a local `.env` file. Default is `0`. **This can only be set in the `docker-compose.yml`.**
 * `ENABLE_LOCALCONF` whether to allow local overrides for web and console configuration (see below). Default is `0`.
 * `YII_DEBUG` whether to enable debug mode for Yii. Default is `0`.
 * `YII_ENV` the Yii app environment. Either `prod` (default) or `dev`
 * `YII_TRACELEVEL` the [traceLevel](http://www.yiiframework.com/doc-2.0/yii-log-dispatcher.html#$traceLevel-detail)
    to use for Yii logging. Default is `0`.
 * `COOKIE_VALIDATION_KEY` the unique [cookie validation key](http://www.yiiframework.com/doc-2.0/yii-web-request.html#$cookieValidationKey-detail) required by Yii. **This variable is mandatory.**
 * `DB_DSN` the [DSN](http://php.net/manual/en/pdo.construct.php) to use for DB connection.
    If this is not set, it will try to create one from docker's `DB_PORT_3306_TCP_ADDR`
    and assume MySQL as DBMS and `web` as DB name. If this is not set either, an exception
    is thrown.
 * `DB_USER` the DB username. Defaults to `web` if not set.
 * `DB_PASSWORD` the DB password. Defaults to `web` if not set.
 * `SMTP_HOST` the SMTP hostname.
 * `SMTP_USER` the username for the SMTP server.
 * `SMTP_PASSWORD` the password for the SMTP server.


### 2.4 Configuration Files

All configuration lives in three files in the `config/` directory.

 * `web.php` configuration for the web application
 * `console.php` configuration for the console application
 * `params.php` application parameters for both web and console application

These three files are committed to your repository. But sometimes it's useful during
development, to override some settings, without accidentally committing them to the
repository. Therefore you can create local override files:

 * `local.php` local overrides for the web configuration
 * `console-local.php` local overrides for the console configuration

To enable this feature, you must set `ENABLE_LOCALCONF` variable to `1` in your
`docker-compose.yml` or `.env` file.

Both files the same format as `web.php` and `console.php` and are merged into the respective
configuration. So you only have to add those parts of the configuration that differ from
the `web.php` and `console.php`. The files are ignored by git to not accidentally
commit any local configs.

### 2.5 PHP-FPM + Nginx Based setups

You can also select the php-fpm base image if you prefer. This will require two
containers, one with php-fpm and one with nginx. There's already a commented out
example in the `docker-compose.yml` file. Also have a look at the Nginx configuration
in `nginx/nginx.conf` which you may want to modify. The most important setting is
the hostname. This must match whatever you have used in your `docker-compose.yml`
for the app container. Default is `app`:


```
fastcgi_pass   app:9000;
```


3. Workflows
------------

Docker is very versatile so you can come up with different workflows.


### 3.1 Dockerfile Based

 * No docker registry required
 * Slower deployment due to extra build step

This is a very simple workflow: In each environment the runtime image is built
from the `Dockerfile`. Developers need to be informed whenever the `Dockerfile` changes
so that they rebuild their local image.

No images are shared between developers, so the initial and also each consecutive build step
could take quite some time, depending on
[how much](https://docs.docker.com/articles/dockerfile_best-practices/#build-cache)
the `Dockerfile` has changed since the last build in this environment.


### 3.2 Docker Image Based

 * Requires a docker registry (either self-hosted or from 3rd party)
 * Quick and simple deployment

Here we can take two approaches: With or without a base image. In both cases
the `Dockerfile` should be organized in a way, that the rather *static* or less changing parts
should be on top of the `Dockerfile` and the *dynamic* or frequently changing
parts (e.g. `COPY . /var/www/html`) at the bottom of the file.


#### 3.2.1 Without A Base Image

We use a single `Dockerfile` and each time we want to make a deployment, we create
a new tagged image and push it to the registry. This image can then be pulled to
production.

One drawback here is, that each deployment image contains a full copy of your
source code, even if you only changed a couple of lines since the last deployed
image.

It's also important that the developers alway pull the last deployed image to
their machine, as otherwhise docker couldn't reuse cached layers the next time
it builds a new image.


#### 3.2.2 Using A Base Image

Here we use two Dockerfiles:

 * One for the base image, e.g. `Dockerfile.base` and
 * one for the final image(s), which extends from the base image

The base image stays the same for some time and is used as basis for
many deployment images. This has the advantage that (hopefully) many
files from the base image can be reused, whenever a new deployment
image is built. So the actual release image layer will have a very
small footprint and, in effect, once a base image is shared among
developers and on production, there's not much data to be moved around
with each release.

To start we'd first move the current `Dockerfile` and create a base image:

```sh
mv Dockerfile Dockerfile.base
docker build -f Dockerfile.base -t myregistry.com:5000/myapp:base-1.0.0
docker push myregistry.com:5000/myapp:base-1.0.0
```

Now we have a base image as version `base-1.0.0`. For ongoing development we take
it from there and use a minimal second `Dockerfile` that is based on this image:

```dockerfile
FROM myregistry.com:5000/myapp:base-1.0.0
COPY composer.json /var/www/html/
COPY composer.lock /var/www/html/
RUN composer self-update && \
    composer install --no-progress
COPY . /var/www/html
RUN mkdir runtime web/assets \
    && chown www-data:www-data runtime web/assets
```

So other developers will now use this simplified `Dockerfile` for their daily work.
They don't have to rebuild all the layers from the base image and will only stack
their local changes on top.

We'll also use this file for the final deployment images:

```sh
docker build -t myregistry.com:5000/myapp:1.0.0
docker build -t myregistry.com:5000/myapp:1.0.1
docker build -t myregistry.com:5000/myapp:1.0.2
...
```

> Note: Version numbers here are only used to make the example clearer.
> You'd probably use a different versioning scheme, especially if you
> do continous deployments.

After some releases your latest code will differ more and more from the base
image. So more and more files will have changed since it was created and this
will make your deployment images bigger and bigger with each release. To avoid
this you can create an updated base image from your latest code:

```sh
docker build -f Dockerfile.base -t myregistry.com:5000/myapp:base-1.1.0
docker push myregistry.com:5000/myapp:base-1.1.0
```

> Note: You may want to modify `Dockerfile.base` to extend from your old
> base image first. This will save extra space, but add more file layers
> to your docker images over time, which is limited to 127.

Now we have an updated base image and can use that for ongoing development.
We therefore have to update the `FROM` line in the `Dockerfile`:

```dockerfile
FROM myregistry.com:5000/myapp:base-1.1.0
```



4. FAQ
------

### 4.1 How Can I Run Yii Console Commands?

```sh
docker-compose run --rm web yii migrate
docker-compose run --rm web yii mycommand/myaction
```


### 4.2 Where Are Composer Packages Installed?

Composer packages are installed inside the container but outside of the shared
directory. During development we usually mount the local app directory into the
container and override any files there. So we would either have to locally install
all the packages in `vendor/` (requiring a local composer installation) or somehow
make sure, that the composer packages are updated after the local dir was shared.

By keeping the directory outside, we circumvent this issue. Docker images will
always contain all required composer dependencies and use those at runtime.


### 4.3 How can I Update Or Install New Composer Packages?

To update or install new packages you first need an updated `composer.lock` file.
Then the next time you issue `docker-compose build` (or manually do `docker build ...`) it will
pick up the changed file and create a new docker image with the updated packages.

Due to the nasty API rate limitation on github, you can't just run `composer` inside
our `web` container. You first have to create a
[personal API token](https://github.com/blog/1509-personal-api-tokens) at github
and expose it in your `docker-compose.yml` file as `API_TOKEN` env var.

Then use our custom `composer` wrapper script as follows (Note the important `./`
before the composer command):

```sh
docker-compose run --rm web ./composer update my/package
```

Now you should have an updated `composer.lock` file and can rebuild your container:

```sh
docker-compose build
```


### 4.4 How Can I Log To A File?

The default log configuration follows the Docker convention to log to `STDOUT`
and `STDERR`. You should better not change this for production. But you can
use a local configuration file as explained above and configure a log route
as usual. If you log to the default directory, you should find the logfiles
in your local `runtime/logs/` directory. Note, that this directory will never
be copied into the container, so there's no risk, that you end up with a
bloated image.


### 4.6 How Can I Add PHP / HHVM Extensions?

Since the [codemix/yii2-base]() image extends from the official [php](https://registry.hub.docker.com/u/library/php/)
image, you can use `docker-php-ext-install` in your Dockerfile. Here's an example:

```
RUN apt-get update \
    && apt-get -y install \
            libfreetype6-dev \
            libjpeg62-turbo-dev \
            libmcrypt-dev \
            libpng12-dev \
        --no-install-recommends \
    && rm -r /var/lib/apt/lists/* \
    && docker-php-ext-install iconv mcrypt \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd
```

For HHVM extension please check the [hhvm](https://registry.hub.docker.com/u/estebanmatias92/hhvm/) base image for details.
