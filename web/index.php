<?php
require('/var/www/vendor/autoload.php');
require('/var/www/html/config/env.php');
require('/var/www/vendor/yiisoft/yii2/Yii.php');

$config = require('/var/www/html/config/web.php');
if (getenv_default('ENABLE_LOCALCONF', false) && file_exists('/var/www/html/config/local.php')) {
    $local = require('/var/www/html/config/local.php');
    $config = yii\helpers\ArrayHelper::merge($config, $local);
}

(new yii\web\Application($config))->run();
