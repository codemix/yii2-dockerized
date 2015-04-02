<?php
require('/var/www/html/helpers/DockerEnv.php');
\DockerEnv::init();
$config = \DockerEnv::webConfig();
(new yii\web\Application($config))->run();
