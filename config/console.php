<?php
/* @var array() $web the web configuration */
$web = self::webConfig();
return [
    'id' => $web['id'],
    'basePath' => $web['basePath'],
    'vendorPath' => $web['vendorPath'],
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'db' => $web['components']['db'],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '/proc/self/fd/2',
                ],
            ],
        ],
    ],
    'params' => $web['params'],
];
