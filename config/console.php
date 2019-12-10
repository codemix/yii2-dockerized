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
                // Route log output to stderr/stdout of 1st process (=docker logs)
                // - popen() opens file descriptor to 'cat > <file>' command
                // - /proc/1/fd/{1,2} are the file descriptors of 1st process stdout/err
                [
                    'class' => 'codemix\streamlog\Target',
                    'fp' => popen('cat > /proc/1/fd/1', 'w'),
                    'logVars' => [],
                    'levels' => ['info', 'trace'],
                    'except' => self::env('CONSOLE_LOG_YII', 0) ? [] : ['yii\*'],
                    'exportInterval' => 1,
                ],
                [
                    'class' => 'codemix\streamlog\Target',
                    'fp' => popen('cat > /proc/1/fd/2', 'w'),
                    'logVars' => [],
                    'levels' => ['error', 'warning'],
                    'except' => self::env('CONSOLE_LOG_YII', 0) ? [] : ['yii\*'],
                    'exportInterval' => 1,
                ],
            ],
        ],
    ],
    'params' => $web['params'],
];
