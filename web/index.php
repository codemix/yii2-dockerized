<?php
require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__.'/../config/env.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__.'/../config/web.php');
if (getenv_default('ENABLE_LOCALCONF', false) && file_exists(__DIR__.'/../config/local.php')) {
    $local = require(__DIR__.'/..config/local.php');
    $config = yii\helpers\ArrayHelper::merge($config, $local);
}

(new yii\web\Application($config))->run();
