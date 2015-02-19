<?php
/**
 * Load .env file and set main YII_* constants
 * You should not have to modify this file
 */
use \Dotenv;

// Only load .env file if enabled and if it exists
if (isset($_ENV['ENABLE_ENV_FILE']) && $_ENV['ENABLE_ENV_FILE'] && file_exists(__DIR__.'/../.env')) {
    Dotenv::load(__DIR__.'/..');
}

/**
 * getenv_default
 *
 * @param string $name the name of the variable to get
 * @param mixed $default the default value to return if variable is not set. Default is null.
 * @return mixed the content of the environment variable or $default if not set
 */
function getenv_default($name, $default=null)
{
    return isset($_ENV[$name]) ? $_ENV[$name] : $default;
}

if (isset($_ENV['YII_DEBUG'])) {
    define('YII_DEBUG', (bool)$_ENV['YII_DEBUG']);
}

if (isset($_ENV['YII_ENV'])) {
    define('YII_ENV', $_ENV['YII_ENV']);
}

if (isset($_ENV['DB_DSN'])) {
    $dsn = $_ENV['DB_DSN'];
} elseif (isset($_ENV['DB_PORT_3306_TCP_ADDR'])) {
    $dsn = 'mysql:host='.$_ENV['DB_PORT_3306_TCP_ADDR'].';dbname=web';
} else {
    throw new \Exception('Neither DB_DSN nor DB_PORT_3306_TCP_ADDR env var is set');
}
define('DB_DSN', $dsn);

Dotenv::required('COOKIE_VALIDATION_KEY');
