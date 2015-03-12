Yii 2 Dockerized
================

A template for docker based Yii 2 applications.

 * Ephemeral container which gets configured via environment variables
 * Optional local configuration overrides for development/debugging (git-ignored)
 * Base scaffold code for login, signup and forgot-password actions
 * Flat configuration file structure
 * Support docker image based development workflow


1. Creating a new project
-------------------------

To use this template, you first need to have [docker](http://www.docker.com) (>=1.5.0) installed.
Then you can start a new project with:

```sh
composer create-project --no-install codemix/yii2-dockerized myproject
```

or, if you don't have composer installed, [download](https://github.com/codemix/yii2-dockerized/releases)
and uncompress the files into a directory. You may then want modify some things to match
your project requirements:

 * the `Dockerfile` e.g. to enable more PHP extensions.
 * the default machine setup in `.docker-composer-example`
 * the default configuration in `config/`
 * the dependencies in `composer.json` (requires update of `composer.lock`, see FAQ below)
 * the example models and initial DB migration
 * the available environment variables
 * the README (this file ;) )

This means, that you can (and probably will!) change the app template given here.
It's based on my [personal Yii 2 base app](https://github.com/mikehaertl/yii2-base-app)
but if you go through the sources you'll find that there's not so much special going on.
So if you prefer a different app template, it should be easy to modify the given
example app.

When you're done, you should commit your initial project to *your* git repository.


2. Setting up the development environment
-----------------------------------------

For local environments we recommend to use [docker-compose](http://docs.docker.com/compose/install/).
Then bringing a new developer into the project is a matter of a few simple steps:

 * Clone the app sources from your repository
 * Create a local `docker-compose.yml` configuration file (see `docker-compose-example.yml`)
 * Add `ENABLE_ENV_FILE=1` to the `environment` section of your `docker-compose.yml`
 * Copy the `.env-example` file to `.env`
 * Run DB migrations (see below)

Now the app can be started with `docker-compose up` and should be available under
[http://localhost:8080](http://localhost:8080) (or the IP address of your VM if you use boot2docker).

As this template follows the [12 factor](http://12factor.net/) principles, all runtime
configuration should happen via environment variables. So in production, you will use
env vars e.g. to pass DB credentials to your container.

For local development you could configure those vars in the `environment` section of your
`docker-compose.yml`. But this requires to rebuild the container whenever you change any var. That's
why we prefer `.env` files (using [phpdotenv](https://github.com/vlucas/phpdotenv)) for local
development. The app will pick up any changes there immediately.

The template so far uses the following configuration variables:

 * `ENABLE_ENV_FILE` whether to load env vars from a local `.env` file. Default is `0`.
 * `ENABLE_LOCALCONF` whether to allow local overrides for web and console configuration (see below). Default is `0`.
 * `YII_DEBUG` whether to enable debug mode for Yii. Default is `0`.
 * `YII_ENV` the Yii app environment. Either `prod` (default) or `dev`
 * `YII_TRACELEVEL` the [traceLevel](http://www.yiiframework.com/doc-2.0/yii-log-dispatcher.html#$traceLevel-detail)
    to use for Yii logging. Default is `0`.
 * `COOKIE_VALIDATION_KEY` the unique [cookie validation key](http://www.yiiframework.com/doc-2.0/yii-web-request.html#$cookieValidationKey-detail) required by Yii. This variable is mandatory.
 * `DB_DSN` the [DSN](http://php.net/manual/en/pdo.construct.php) to use for DB connection.
    If this is not set, it will try to create one from docker's `DB_PORT_3306_TCP_ADDR`
    and assume MySQL as DBMS and `web` as DB name. If this is not set either, an exception
    is thrown.
 * `DB_USER` the DB username. Defaults to `web` if not set.
 * `DB_PASSWORD` the DB password. Defaults to `web` if not set.
 * `SMTP_HOST` the SMTP hostname.
 * `SMTP_USER` the username for the SMTP server.
 * `SMTP_PASSWORD` the password for the SMTP server.


3. Configuration files
----------------------

All configuration lives in 3 files in the `config/` directory.

 * `web.php` configuration of the web app
 * `console.php` configuration of the console app (reused parts of `web.php`, see examples)
 * `params.php` application parameters for both web and console application

For maximum flexibility you can also use local overrides for web and console configuration
files. You therefore first need to set the `ENABLE_LOCALCONF` variable to `1` in your
`docker-compose.yml` or `.env` file. Then the app will check if any of the following files exists:

 * `local.php` optional local overrides to the web config
 * `console-local.php` an optional file with local overrides to the console configuration

Both have the same format as `web.php` and `console.php` and are merged into the respective
configuration. So you only have to add those parts of the configuration that differ from
the `web.php` and `console.php`. The files are ignored by git to not accidentally
commit any local configs.


4. Workflows
------------

Docker is very versatile so you can come up with different workflows.


### 4.1 Dockerfile based

 * No docker registry required
 * Slower deployment due to extra build step

This is a very simple workflow: In each environment the runtime image is built
from the `Dockerfile`. Developers need to be informed whenever the `Dockerfile` changes
so that they rebuild their local image.

No images are shared between developers, so the initial and also each consecutive build step
could take quite some time, depending on
[how much](https://docs.docker.com/articles/dockerfile_best-practices/#build-cache)
the `Dockerfile` has changed since the last build in this environment.


### 4.2 Docker image based

 * Requires a docker registry (either self-hosted or from 3rd party)
 * Quick and simple deployment

Here we can take two approaches: With or without a base image. In both cases
the `Dockerfile` should be organized in a way, that the rather *static* or less changing parts
should be on top of the `Dockerfile` and the *dynamic* or frequently changing
parts (e.g. `COPY . /var/www/html`) at the bottom of the file.


#### 4.2.1 Without a base image

We use a single `Dockerfile` and each time we want to make a deployment, we create
a new tagged image and push it to the registry. This image can then be pulled to
production.

One drawback here is, that each deployment image contains a full copy of your
source code, even if you only changed a couple of lines since the last deployed
image.

It's also important that the developers alway pull the last deployed image to
their machine, as otherwhise docker couldn't reuse cached layers the next time
it builds a new image.


#### 4.2.2 Using a base image

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

### 5.1 How can i run yii console commands?

```sh
docker-compose run --rm web yii migrate
docker-compose run --rm web yii mycommand/myaction
```


### 5.2 Where are composer packages installed?

Composer packages are installed inside the container but outside of the shared
directory. During development we usually mount the local app directory into the
container and override any files there. So we would either have to locally install
all the packages in `vendor/` (requiring a local composer installation) or somehow
make sure, that the composer packages are updated after the local dir was shared.

By keeping the directory outside, we circumvent this issue. Docker images will
always contain all required composer dependencies and use those at runtime.


### 5.3 How can I update or install new composer packages?

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


### 5.4 I have a permission issue with runtime / web/assets directory. How to fix it?

This is caused by a missing feature in docker: You can not set the permissions of
shared volumes. As a workaround make the directories world writeable:

```sh
chmod a+rwx runtime/ web/assets/
```


### 5.5 How can I log to a file?

The default log configuration follows the Docker convention to log to `STDOUT`
and `STDERR`. You should better not change this for production. But you can
use a local configuration file as explained above and configure a log route
as usual. If you log to the default directory, you should find the logfiles
in your local `runtime/logs/` directory. Note, that this directory will never
be copied into the container, so there's no risk, that you end up with a
bloated image.

