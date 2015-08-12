<?php
use yii\helpers\ArrayHelper;

/**
 * DockerEnv
 *
 * This is a utility class for the yii2-dockerized app that helps to load Yii2, initialize
 * main Yii 2 constants, load config files and obtain environment variables.
 */
class DockerEnv
{
    const APP_DIR = '/var/www/html/';
    const VENDOR_DIR = '/var/www/vendor/';

    const TEST_DB_DSN = 'mysql:host=testdb;dbname=test';
    const TEST_DB_USER = 'test';
    const TEST_DB_PASSWORD = 'test';

    /**
     * Initialize main environment vars and load Yii 2
     */
    public static function init()
    {
        require('/var/www/vendor/autoload.php');

        // Load .env file if enabled and if it exists
        if (getenv('ENABLE_ENV_FILE') && file_exists(self::APP_DIR.'.env')) {
            Dotenv::load(self::APP_DIR);
        }

        // Define main environment variables
        if (isset($_ENV['YII_DEBUG'])) {
            define('YII_DEBUG', (bool)$_ENV['YII_DEBUG']);
            if (YII_DEBUG) {
                error_reporting(E_ALL);
            }
        }
        if (isset($_ENV['YII_ENV'])) {
            define('YII_ENV', $_ENV['YII_ENV']);
        }
        define('YII_ENV_TEST', defined('YII_ENV') && YII_ENV==='test');

        require(self::VENDOR_DIR.'yiisoft/yii2/Yii.php');
    }

    /**
     * Load the web configuration
     *
     * @param array $_files list of filenames with configuration overrides
     * @param array $_config overrides for the web configuration array. This also overrides the $_file configuration.
     * @return array the web configuration
     */
    public static function webConfig($_files = [], $_config = [])
    {
        if (YII_ENV_TEST) {
            array_unshift($_files, self::APP_DIR.'tests/codeception/config/acceptance.php');
        } else {
            if (self::get('ENABLE_LOCALCONF', false) && file_exists(self::APP_DIR.'config/local.php')) {
                array_unshift($_files, self::APP_DIR.'config/local.php');
            }
            array_unshift($_files, self::APP_DIR.'config/web.php');
        }

        return self::loadConfigs($_files, $_config);
    }

    /**
     * Load the console configuration
     *
     * @param string|null $_file a configuration file with overrides for the console configuration.
     * @param array $_config overrides for the console configuration array. This also overrides the $_file configuration.
     * @return array the console configuration
     */
    public static function consoleConfig($_files = [], $_config = [])
    {
        if (self::get('ENABLE_LOCALCONF', false) && file_exists(self::APP_DIR.'config/console-local.php')) {
            array_unshift($_files, self::APP_DIR.'config/console-local.php');
        }
        array_unshift($_files, self::APP_DIR.'config/console.php');
        return self::loadConfigs($_files, $_config);
    }

    /**
     * Utility method to return either an env var or a default value if the var is not set.
     *
     * @param string $key the name of the variable to get
     * @param mixed $default the default value to return if variable is not set. Default is null.
     * @param bool $required whether the var must be set. $default is ignored in this case. Default is `false`.
     * @return mixed the content of the environment variable or $default if not set
     */
    public static function get($key, $default = null, $required = false)
    {
        if ($required) {
            Dotenv::required($key);
        }
        return isset($_ENV[$key]) ? $_ENV[$key] : $default;
    }
    /**
     * Load configuration files and merge them together.
     *
     * @param array $_files list of configuration files to load and merge. Later files override previous configuration.
     * @param array $_config optional additional configuration to merge into the result
     * @return array the resulting configuration array
     */
    protected static function loadConfigs($_files, $_config = [])
    {
        $_configs = array_map(function ($f) { return require($f); }, $_files);
        $_configs[] = $_config;
        return call_user_func_array('yii\helpers\ArrayHelper::merge', $_configs);
    }

    /**
     * The dsn to use for the DB connection. This is either
     *
     *  - DB_DSN environment variable, or, if not set
     *  - self::TEST_DB_DSN, if in testing environment, or
     *  - the $dsn parameter
     *
     * @param string $dsn the dsn to return if no env var is set and not in testing mode. Default is 'mysql:host=db;dbname=web'.
     * @return string the DB  dsn for the current environment
     */
    public static function dbDsn($dsn = 'mysql:host=db;dbname=web')
    {
        return self::get('DB_DSN', YII_ENV_TEST ? self::TEST_DB_DSN : $dsn);
    }

    /**
     * The username to use for the DB connection. This is either
     *
     *  - DB_USER environment variable, or, if not set
     *  - self::TEST_DB_PASSWORD, if in testing environment, or
     *  - the $user parameter
     *
     * @param string $user the user to return if no env var is set and not in testing mode. Default is 'web'.
     * @return string the db username for the current environment
     */
    public static function dbUser($user = 'web')
    {
        return self::get('DB_USER', YII_ENV_TEST ? self::TEST_DB_USER : $user);
    }

    /**
     * The password to use for the DB connection. This is either
     *
     *  - DB_PASSWORD environment variable, or, if not set
     *  - self::TEST_DB_PASSWORD, if in testing environment, or
     *  - the $password parameter
     *
     * @param string $password the password to return if no env var is set and not in testing mode. Default is 'web'.
     * @return string the db password for the current environment
     */
    public static function dbPassword($password = 'web')
    {
        return self::get('DB_PASSWORD', YII_ENV_TEST ? self::TEST_DB_PASSWORD : $password);
    }
}
