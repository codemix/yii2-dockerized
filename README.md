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

    composer create-project --prefer-dist codemix/yii2-dockerized .

You should then update things for the requirements of your project:

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

### Docker images

 * Requires docker registry (either self-hosted or for $$$)
 * Smaller image footprints
 * TBC

### Dockerfiles only

 * No registry required
 * Building images takes longer - influences deployment!
 * TBC

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

