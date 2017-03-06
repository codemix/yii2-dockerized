<?php
use codemix\yii2confload\Config;

require('/var/www/vendor/autoload.php');

// Init configuration and load Yii bootstrap file
$config = Config::bootstrap('/var/www/html');

Yii::createObject('yii\web\Application', [$config->web()])->run();
