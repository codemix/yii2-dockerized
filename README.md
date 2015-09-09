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

Quickstart
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

Please check the [Wiki](https://github.com/codemix/yii2-dockerized/wiki) for full documentation.
