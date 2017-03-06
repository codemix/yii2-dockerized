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
            'targets' => [
                [
                    'class' => 'codemix\streamlog\Target',
                    'url' => 'php://stdout',
                    'levels' => ['info','trace'],
                    'categories' => ['app\\*'],
                    'logVars' => [],
                ],
                [
                    'class' => 'codemix\streamlog\Target',
                    'url' => 'php://stderr',
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                ],
            ],
        ],
    ],
    'params' => $web['params'],
];
