<?php
/* @var codemix\yii2confload\Config $this */

$web = $this->web();
return [
    'id' => $web['id'],
    'aliases' => $web['aliases'],
    'basePath' => $web['basePath'],
    'vendorPath' => $web['vendorPath'],
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'db' => $web['components']['db'],
        'log' => [
            'traceLevel' => self::env('YII_TRACELEVEL', 0),
            'flushInterval' => 1,   // log messages immediately
            'targets' => [
                [
                    'class' => 'codemix\streamlog\Target',
                    'url' => 'php://stdout',
                    'logVars' => [],
                    'exportInterval' => 1,
                    'except' => self::env('CONSOLE_LOG_YII', 0) ? [] : ['yii\*'],
                ],
            ],
        ],
    ],
    'params' => $web['params'],
];
