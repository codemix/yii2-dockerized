Yii 2 Dockerized
================

[![Latest Stable Version](https://poser.pugx.org/codemix/yii2-dockerized/v/stable.svg)](https://packagist.org/packages/codemix/yii2-dockerized)
[![Total Downloads](https://poser.pugx.org/codemix/yii2-dockerized/downloads.svg)](https://packagist.org/packages/codemix/yii2-dockerized)

A template for Yii 2 applications based on the
[codemix/yii2-base](https://registry.hub.docker.com/u/codemix/yii2-base/) docker image.

 * Ephemeral container, configured via environment variables
 * Testing container for easy testing
 * Optional local configuration overrides for development/debugging (git-ignored)
 * Base scaffold code for login, signup and forgot-password actions
 * Flat configuration file structure
 * Supports docker image based development workflow

The `yii2-base` image comes in three flavours:

 * **Apache with PHP module** (based on `php:5.6.10-apache`)
 * **PHP-FPM** (based on `php:5.6.10-fpm`)
 * **HHVM** (based on `estebanmatias92/hhvm:3.7.0-fastcgi`)

1. Quickstart
-------------

You need to have [docker](http://www.docker.com) (>=1.5.0) and
[docker-compose](https://docs.docker.com/compose/install/) installed.

```sh
composer create-project --no-install codemix/yii2-dockerized myproject
cd myproject
cp docker-compose-example.yml docker-compose.yml
cp .env-example .env
docker-compose up
# From another terminal window:
docker-compose run --rm web ./yii migrate
```

It may take some minutes to download the required docker images. When
done, you can access the new app from [http://localhost:8080](http://localost:8080).

> *Note:* On Windows and Mac OS you have to use [boot2docker](https://github.com/boot2docker/boot2docker).
> There's also a [workaround](https://github.com/artsemis/docker-docker-compose) to make `docker-compose` available.


2. How To Use
-------------

This project is a template for your own Yii2 applications. You can use it as a starting
point and modify it to fit your requirements.


### 2.1 Starting A New Project

> **Note**: You will do this only *once* in the lifetime of a project!

As shown in the quickstart you will first create a new project with `composer` or, if you don't
have composer installed, [download](https://github.com/codemix/yii2-dockerized/releases)
and uncompress the files into a new directory.

Before you commit the app into *your* repository, you first will want to set up the project
for your purpose.

The first step is to select the `yii2-base` image flavour in the `Dockerfile`. So uncomment
the apropriate line:

```
FROM codemix/yii2-base:2.0.6-apache
#FROM codemix/yii2-base:2.0.6-php-fpm
#FROM codemix/yii2-base:2.0.6-hhvm
```

Then you should check the following files and directories:

 * `Dockerfile`:  Optionally add PHP extensions
 * `docker-compose-example.yml`: Provide an example machine configuration for other developers
 * `config/`: Update the default configuration
 * `.env-example`: Optionally add more environment variables
 * `composer.json`: Optionally add more dependencies for your project
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

The local app directory is mapped into the `/var/www/html` directory of the `web` container. So you can
now work on your local source files and will immediately see the results under the URL of the container.


### 2.3 Environment Variables

This template follows the [12 factor](http://12factor.net/) principles. This means, that
all dynamic configuration parameters (for example database credentials or secret
API keys) are passed to the container through **environment variables**.

There are two options how you can set environment variables **during development**:

 * In the `environment` section of the `docker-compose.yml`. This requires to remove and
   recreate the container each time you change a variable. So we recommend to only put
   `ENABLE_ENV_FILE` here and use the next option for the rest.
 * In a `.env` file in your app directory. Changes there are picked up immediately by the
   yii app inside the container. But any other command there will usually ignore this file.
   We have included an example `.env-example` that you can use to get started.

Variables in `docker-compose.yml` have precedence over those in the `.env` file.

**In production** you will use whatever means are neccessary to set the environment variables.
This depends on how you host your docker image (or if you use docker in production at all).

#### 2.3.1 List of environment variables

> Note: Some variables can only be set in the `docker-compose.yml` (marked with **`dc-only`**).

**Mandatory:**

 * `COOKIE_VALIDATION_KEY` the unique [cookie validation key](http://www.yiiframework.com/doc-2.0/yii-web-request.html#$cookieValidationKey-detail) required by Yii.

**Optional:**

 * `API_TOKEN` (**`dc-only`**) the github API token for composer updates.
 * `ENABLE_ENV_FILE` (**`dc-only`**) whether to load env vars from a local `.env` file. Default is `0`.
 * `ENABLE_LOCALCONF` whether to allow local overrides for web and console configuration (see below). Default is `0`.
 * `YII_DEBUG` whether to enable debug mode for Yii. Default is `0`.
 * `YII_ENV` the Yii app environment. Either `prod` (default) or `dev`. Do not use `test` as this is reserved for the testing container!
 * `YII_TRACELEVEL` the [traceLevel](http://www.yiiframework.com/doc-2.0/yii-log-dispatcher.html#$traceLevel-detail)
    to use for Yii logging. Default is `0`.
 * `DB_DSN` the [DSN](http://php.net/manual/en/pdo.construct.php) to use for DB connection. Defaults to `mysql:host=db;dbname=web` if not set.
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
repository. Therefore you can create local override files (requires `ENABLE_LOCALCONF`
to be set):

 * `local.php` local overrides for the web configuration
 * `console-local.php` local overrides for the console configuration

Both files are merged with the respective configuration. Thus you can override specific
parts of the web or console configuration there.


### 2.5 PHP-FPM + Nginx Based setups

If you prefer to use the php-fpm base image, you will need two containers, one for the
php-fpm process and one for nginx. There's already a commented out example in the
`docker-compose-example.yml` file.

You may also want to modify the Nginx configuration in `nginx/nginx.conf`. The most important
setting there is the hostname of the php-fpm container. This must match whatever you have used
in your `docker-compose.yml` for the app container. Default is `app`:

```
fastcgi_pass   app:9000;
```

3. Testing
----------

We have included a basic test setup that gets you started with testing in no time.
It uses another `docker-compose.yml` where the test infrastructure is defined.

> **Note:** If you host more than one yii2-dockerized project on your host
> you should set the `COMPOSE_PROJECT` envirnoment variable to a unique name for
> each project, before you run any of the below commands. Otherwhise you will
> get conflicts as each test container will automatically be called like
> `tests_test_1` due to the same `tests/` directory name.

### 3.1 Setup test database

Before you run the tests for the first time, you have to set up the DB:

```sh
cd tests/
docker-compose run --rm test ./yii migrate
```

> **Note:** This may fail the first time because the DB is not up.
> In this case try again. If this doesn't help either, try to run
> `docker-compose up` in another terminal window first, then try
> the above command again.

### 3.2 Writing tests

To write tests, you simply create the respective classes in the `acceptance`,
`functional` and `unit` directories in the `tests/codeception` folder. You may
also have to provide [Page](http://www.yiiframework.com/doc-2.0/yii-codeception-basepage.html)
classes for acceptance and functional tests in the `_pages` directory or add some
[fixtures](http://www.yiiframework.com/doc-2.0/guide-test-fixtures.html) in the
`fixtures` directory.

We have included some simple examples that should help you to get started. For further
details on how to write tests, please refer to the [codeception](http://codeception.com/)
and [Yii 2](http://www.yiiframework.com/doc-2.0/guide-test-overview.html) documentation.

### 3.3 Running tests

To run test you only need one simple command inside the `tests/` directory:

```sh
docker-compose run --rm test
```

You can also specify a specific `codecept` command:

```sh
docker-compose run --rm test codecept run functional
```

### 3.4 Files and Directories

The many files in the `tests/` directory can be overwhelming. So here's a summary
of what each file and directory is used for.

```
  - tests
     - codeception/             the codeception/ directory
        - _output/              temporary outputs (gitignored)
        - _pages/               pages shared by acceptance and functional tests
        - acceptance/           acceptance tests
        - config/               Yii app configuration ...
           acceptance.php       ... for acceptance tests
           config.php           ... shared by all tests
           functional.php       ... for functional tests
           unit.php             ... for unit tests
        - fixtures/             fixtures for all tests
           - templates/         templates for yii2-faker
        - functional/           functional tests
        - unit/                 unit tests
        _bootstrap.php          bootstrap file for all tests (load env and Yii.php)
        acceptance.suite.yml    configuration for acceptance tester
        functional.suite.yml    configuration for functional tester
        unit.suite.yml          configuration for unit tester
     codeception.yml            main configuration for codeception
     docker-compose.yml         docker compose configuration for testing
     yii                        CLI for the testing environment (migrations, fixtures, ...)
```

### 3.5 Creating fixtures

Before you can generate fixtures, you have to provide the respective template
files for [yii2-faker](https://github.com/yiisoft/yii2-faker) in the `codeception/templates`
directory. Then you can create fixtures with:

```sh
docker-compose run --rm test ./yii fixture/generate <tablename>
```


4. Workflows
------------

Docker is very versatile so you can come up with different workflows.


### 4.1 Dockerfile Based

 * No docker registry required
 * Slower deployment due to extra build step

This is a very simple workflow: In each environment the runtime image is built
from the `Dockerfile`. Developers need to be informed whenever the `Dockerfile` changes
so that they rebuild their local image.

No images are shared between developers, so the initial and also each consecutive build step
could take quite some time, depending on
[how much](https://docs.docker.com/articles/dockerfile_best-practices/#build-cache)
the `Dockerfile` has changed since the last build in this environment.


### 4.2 Docker Image Based

 * Requires a docker registry (either self-hosted or from 3rd party)
 * Quick and simple deployment

Here we can take two approaches: With or without a base image. In both cases
the `Dockerfile` should be organized in a way, that the rather *static* or less changing parts
should be on top of the `Dockerfile` and the *dynamic* or frequently changing
parts (e.g. `COPY . /var/www/html`) at the bottom of the file.


#### 4.2.1 Without A Base Image

We use a single `Dockerfile` and each time we want to make a deployment, we create
a new tagged image and push it to the registry. This image can then be pulled to
production.

One drawback here is, that each deployment image contains a full copy of your
source code, even if you only changed a couple of lines since the last deployed
image.

It's also important that the developers alway pull the last deployed image to
their machine, as otherwhise docker couldn't reuse cached layers the next time
it builds a new image.


#### 4.2.2 Using A Base Image

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



5. FAQ
------

### 5.1 How Can I Run Yii Console Commands?

```sh
docker-compose run --rm web yii migrate
docker-compose run --rm web yii mycommand/myaction
```


### 5.2 Where Are Composer Packages Installed?

Composer packages are installed inside the container under `/var/www/vendor`.
So they live outside of the app directory. This is because during development we
usually mount the local app directory into the `/var/www/html` directory of the
container. This would override any vendor packages we have there. So you would
either have to locally install all the packages in `vendor/` or somehow
make sure, that the composer packages are updated after the local dir was shared.

By keeping the directory outside, we circumvent this issue. The docker images will
always contain all required composer dependencies and use those at runtime.


### 5.3 How can I Update Or Install New Composer Packages?

To update or install new packages you first need an updated local `composer.lock` file.
Then the next time you issue `docker-compose build` it will pick up the changed
file and create a new docker image with the updated packages inside:

```sh
docker-compose run --rm web composer update my/package
docker-compose build
```

> Note: If you face the nasty API rate limit issue with github, you can create a
[personal API token](https://github.com/blog/1509-personal-api-tokens)
and expose it in your `docker-compose.yml` file as `API_TOKEN` env var.

### 5.4 How can I make composer packages available locally?

Some IDE's may require a copy of the vendor directory somewhere on your local
host for autocompleting commands or providing inline help. You can therefore copy
the vendor directory to your local directory:

```sh
docker-compose run --rm web cp -r /var/www/vendor .
```

### 5.5 How Can I Log To A File?

The default log configuration follows the Docker convention to log to `STDOUT`
and `STDERR`. You should better not change this for production. But you can
use a local configuration file as explained above and configure a log route
as usual. If you log to the default directory, you should find the logfiles
in your local `runtime/logs/` directory. Note, that this directory will never
be copied into the container, so there's no risk, that you end up with a
bloated image.


### 5.6 How Can I Add PHP / HHVM Extensions?

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
