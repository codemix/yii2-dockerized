Yii 2 Dockerized
================

[![Latest Stable Version](https://poser.pugx.org/codemix/yii2-dockerized/v/stable.svg)](https://packagist.org/packages/codemix/yii2-dockerized)
[![Total Downloads](https://poser.pugx.org/codemix/yii2-dockerized/downloads.svg)](https://packagist.org/packages/codemix/yii2-dockerized)

A template for docker based Yii 2 applications.

 * Ephemeral container, configured via environment variables
 * Dedicated base image to configure all required PHP extensions and composer packages
 * Optional local configuration overrides for development/debugging (git-ignored)
 * Base scaffold code for login, signup and forgot-password actions
 * Flat configuration file structure

# 1 Main Concepts

## 1.1 Base Image

The core idea of this template is that you build a bespoke base image for your
application wich exactly meets your project's requirements. We therefore use a
dedicated configuration in `./build/Dockerfile`. This image will never change
unless

 * you want to upgrade to a newer Yii version or
 * you want to add a missing PHP extension.

The actual application image will extend from this base and use the Dockerfile
in `./Dockerfile`.

In the recommended scenario, you would build the base image, upload it to your
own container registry and reference it in the app's main `./Dockerfile`. This
way all your coworkers will always share the same base image.

If you don't have a container registry you can still use the template. But then
each developer in your team will have to build the base image locally.

## 1.2 Configuration with Environment Variables

We follow docker's principle that containers are usually configured via
environment variables. This does not include each and every part of the
application configuration of course: Most settings will be hardcoded in
`./config/web.php`, for example URL rules. But the variable runtime parameters
like DB credentials or whether to enable debug mode will be configurable via
environment variables.

You should continue to follow this principle when developing your app. For
more details also see our
[yii2-configloader](https://github.com/codemix/yii2-configloader) that we use
in this template.

## 1.3 Cleanup and Initial Commit to Your Repository

At this point you may want to clean up our template and remove or modify those
parts that you don't need. Afterwards you are ready for the initial commit to
your project repository.


# 2 Initial Project Setup

First fetch a copy of our application template, for example with composer:

```sh
composer create-project --no-install codemix/yii2-dockerized myproject
```

You could also just download the repo from github.


## 2.1 Prepare Composer Directory

The contents of `./build/vendor` will be copied into the base image we build
below. So we first need to install all required composer packages there.

To do so you can use the [composer](https://hub.docker.com/r/library/composer/)
container:

```sh
cd myproject/base
docker-compose run --rm composer update
```

This way you start your project with the latest Yii2 dependencies. You can now
also add more packages that are application may depend on:

```sh
docker-compose run --rm composer require some/package
```

> **Note:** As docker's composer image may not meet the PHP requirements of
> all your packages you may have to add `--ignore-platform-reqs` to be able to
> install some packages.

## 2.2 Build the Base Image

Before you continue with building the base image you should:

 * Set a name for the base image in `./build/docker-compose.yml`
 * Optionally add more PHP extensions in `./build/Dockerfile`

Building the image is straightforward:

```sh
cd myproject/base
docker-compose build
```

Now you could upload that image to your container registry.


# 3 Local Development

During development we will map the local app directory into the app container
so that we always run the code that we currently work on. This container will
use the actual app image which gets built from the `./Dockerfile` in the main
app directory.

As local environments may differ (e.g. use different docker network settings)
we usually keep `docker-compose.yml` out of version control. Instead we provide
a `docker-compose-example.yml` with a reasonable example setup.

Since the runtime configuration should happen with environment variables, we
use a `.env` file. We also keep this out of version control and only include a
`.env-example` to get developers started.

## 3.1 Initial Setup of a Development Environment

After you've cloned your project you need to prepare two files:

```sh
cd myproject
cp docker-compose-example.yml docker-compose.yml
cp .env-example .env
```

You should modify those two files e.g. to enable debug mode or configure a
database. Then you should be ready to start your container and initialize
your database (if your project has one):

```sh
docker-compose up -d
docker-compose exec web ./yii migrate
```

It may take some minutes to download the required docker images before you can
issue the second command. When done, you can access the new app from
[http://localhost:8080](http://localhost:8080).

If you see an error about write permissions to `web/assets/` or `runtime/` it's
because the local file owner id is different from `1000` which is the
`www-data` user in the container. To fix this, run:

```
docker-compose exec chown www-data web/assets runtime
```

## 3.2 Development Session

A development session will then usually go like this:

```sh
git pull --ff-only
docker-compose up -d
# edit files, check, tests, ...
git add .
git commit -m 'Implemented stuff'
git push origin my-branch
docker-compose stop
```

### 3.2.1 Running yiic Commands

If you need to run yiic commands you simply execute them in the running
development container:

```sh
docker-compose exec web ./yiic migrate/create add_column_name
```

> **Note:** You may have to fix local write permissions/ownership for files
> that were created from inside the container like `./migrations/*` in this
> example.

### 3.2.2 Adding or Updating Composer Packackes

As noted above this requires a rebuild of the base image. Simply follow
the same steps as described in the initial project setup.

### 3.2.3 Working with Complex Local Configuration

Sometimes you may have to add complex parts to `config/web.php` but want
to avoid the risk of accidentally committing them. You therefore can activate
support for a `config/local.php` file in your `.env`. This config file will
be merged into `config/web.php`.

```sh
# Whether to load config/(local|console-local).php. Default is 0.
ENABLE_LOCALCONF=1
```

### 3.2.3 Make Composer Packages Available Locally

For some IDEs it's useful to have the composer packages available on your local
host system. To do so you can simply copy them from inside the container:

```
docker-compose exec web cp -rf /var/www/vendor ./
```

> **Note:** Inside the container composer packages live in `/var/www/vendor`
> instead of the app's `./vendor` directory. This way we don't override the
> vendor directory when we map the app directory into the container.
