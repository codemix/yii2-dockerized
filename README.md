Yii 2 Dockerized
================

A template for docker based Yii 2 applications.

 * Ephemeral container which gets configured via environment variables
 * Optional local configuration overrides for development/debugging (git-ignored)
 * Base scaffold code for login, signup and forgot-password actions
 * Flat configuration file structure
 * Support docker image based development workflow


Creating a new project
----------------------

To use this template, you first need to have [docker](http://www.docker.com) installed.
Then you can start a new project with:

    composer create-project --no-install codemix/yii2-dockerized myproject

or, if you don't have composer installed, [download](https://github.com/codemix/yii2-dockerized/releases)
and uncompress the files into a directory. You should then update things for the requirements of your project:

 * Modify the default configuration in `config/` and `.env-example`
 * Modify the dependencies in `composer.json`
 * Modify the default machine setup in `.fig-example`
 * Modify `Dockerfile` e.g. to enable more PHP extensions
 * Modify the example models and initial DB migration
 * Modify the README (this file ;) )

When you're done, you should commit your initial project to your git repository.


Setting up the development environment
--------------------------------------

For local environments we recommend to use [fig](http://www.fig.sh/). Then
bringing a new developer into the project is a matter of a few simple steps:

 * Clone the app sources from your repository
 * Create a local `fig.yml` configuration file (see `fig-example.yml`)
 * Create a local `.env` file (see `.env-example`)
 * Run DB migrations (see below)

Now the app can be started with `fig up`. Environment variables can either be
set in the `fig.yml` or - since we use [phpdotenv](https://github.com/vlucas/phpdotenv) -
in a local `.env` file (see the example in `.env-example`).

The included example configuration makes use of docker's default environment
variables for linked containers like `DB_PORT_3306_TCP_ADDR`. So if you use
the above `fig.yml`, you don't have to set any DB related variables at all.


Configuration files
-------------------

All configuration lives in 3 files in the `config/` directory.

 * `web.php` configuration of the web app
 * `console.php` configuration of the console app (reused parts of `web.php`, see examples)
 * `params.php` application parameters for both web and console application

For maximum flexibility you can also use local overrides for web and console configuration
files. You therefore first need to set the `IGNORE_LOCAL_CONFIG` variable to `0` in your
`fig.yml` or `.env` file. Then the app will check if any of the following files exists:

 * `local.php` optional local overrides to the web config
 * `console-local.php` an optional file with local overrides to the console configuration

Both files are ignored by git.


Workflows
---------

Docker is very versatile so you can come up with different workflows.


### Dockerfiles only

 * No docker registry required
 * Slower deployment due to extra build step

This is a very simple workflow: In each environment the runtime image is built
from the Dockerfile. Developers need to be informed whenever the Dockerfile changes
so that they rebuild their local image.

No images are shared, so the build step could take quite some time, depending on
[how much](https://docs.docker.com/articles/dockerfile_best-practices/#build-cache)
the Dockerfile has changed.


### Docker images

 * Requires a docker registry (either self-hosted or from 3rd party)
 * Quick and simple deployment

Here we can take two approaches: With or without a base image. In both cases
the Dockerfile should be organized in a way, that the "static", less changing parts
should be on top of the Dockerfile and the "variable" or frequently changing
parts (e.g. `COPY . /var/www/html`) are on the bottom of the file.


#### Without a base image

We use a single Dockerfile and each time we want to make a deployment, we create
a new tagged image and push it to the registry. This image can then be pulled to
production.

One drawback here is, that each deployment image contains a full copy of your
source code, even if you only changed a couple of lines since the last deployed
image.

It's also important that the developers alway pull the last deployed image to
their machine, as otherwhise docker couldn't reuse cached layers the next time
it builds a new image.


#### Using a base image

Here we use two Dockerfiles:

 * One for the base image, e.g. `Dockerfile.base` and
 * one for the final image, which extends from the base image

So we'd first move the current Dockerfile and create a base image:

    mv Dockerfile Dockerfile.base
    docker build -f Dockerfile.base -t myregistry.com:5000/myapp:base-1.0.0
    docker push myregistry.com:5000/myapp:base-1.0.0

Now we have a base image as version `base-1.0.0`. For ongoing development we take
it from there and use a minimal second Dockerfile that is based on this image:

```
FROM myregistry.com:5000/myapp:base-1.0.0
COPY composer.json /var/www/html/
COPY composer.lock /var/www/html/
RUN composer self-update && \
    composer install --no-progress
COPY . /var/www/html
RUN mkdir runtime web/assets \
    && chown www-data:www-data runtime web/assets
```

So other developers will now use this simplified Dockerfile for their daily work.
They don't have to rebuild all the layers from the base image and will only stack
their local changes on top.

We'll also use this file for the final deployment images:

    docker build -t myregistry.com:5000/myapp:1.0.0
    docker build -t myregistry.com:5000/myapp:1.0.1
    docker build -t myregistry.com:5000/myapp:1.0.2
    ...

After some time, when the modifications to the `base-1.0.0` image exceed a certain
volume, we can create another base image:

    docker build -f Dockerfile.base -t myregistry.com:5000/myapp:base-1.1.0
    docker push myregistry.com:5000/myapp:base-1.1.0

And modify the `FROM` line in the Dockerfile to use this image as basis:

    FROM myregistry.com:5000/myapp:base-1.1.0



FAQ
---

### How can i run yii console commands?

    fig run --rm web yii migrate
    fig run --rm web yii mycommand/myaction


### Where are composer packages installed?

Composer packages are installed inside the container but outside of the shared
directory. During development we usually mount the local app directory into the
container and override any files there. So we would either have to locally install
all the packages in `vendor/` (requiring a local composer installation) or somehow
make sure, that the composer packages are updated after the local dir was shared.

By keeping the directory outside, we circumvent this issue. Docker images will
always contain all required composer dependencies and use those at runtime.


### How can I update or install new composer packages?

To update or install new packages you first need an updated `composer.lock` file.
Then the next time you issue `fig build` (or manually do `docker build ...`) it will
pick up the changed file and create a new docker image with the updated packages.

Due to the nasty API rate limitation on github, you can't just run `composer` inside
our `web` container. You first have to create a
[personal API token](https://github.com/blog/1509-personal-api-tokens) at github
and expose it in your `fig.yml` file as `API_TOKEN` env var.

Then use our custom `composer` wrapper script as follows (Note the important `./`
before the composer command):

    fig run --rm web ./composer update my/package

Now you should have an updated `composer.lock` file and can rebuild your container:

    fig build


### I have a permission issue with runtime / web/assets directory. How to fix it?

This is caused by a missing feature in docker: You can not set the permissions of
shared volumes. As a workaround make the directories world writeable:

    chmod a+rwx runtime/ web/assets/


### How can I log to a file?

The default log configuration follows the Docker convention to log to `STDOUT`
and `STDERR`. You should better not change this for production. But you can
use a local configuration file as explained above and configure a log route
as usual. If you log to the default directory, you should find the logfiles
in your local `runtime/logs/` directory. Note, that this directory will never
be copied into the container, so there's no risk, that you end up with a
bloated image.

