<?php
/* @var codemix\yii2confload\Config $this */
$config = [
    'id' => 'basic',
    'aliases' => [
        '@bower' => '/var/www/vendor/bower-asset',
        '@npm' => '/var/www/vendor/npm-asset',
    ],
    'basePath' => '/var/www/html',
    'bootstrap' => ['log'],
    'vendorPath' => '/var/www/vendor',
    'catchAll' => self::env('MAINTENANCE', false) ? ['site/maintenance'] : null,
    'components' => [
        'cache' => self::env('DISABLE_CACHE', false) ?
            'yii\caching\DummyCache' :
            [
                'class' => 'yii\caching\ApcCache',
                'useApcu' => true,
            ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => self::env('DB_DSN', 'mysql:host=db;dbname=web'),
            'username' => self::env('DB_USER', 'web'),
            'password' => self::env('DB_PASSWORD', 'web'),
            'charset' => 'utf8',
            'tablePrefix' => '',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => self::env('YII_TRACELEVEL', 0),
            'targets' => [
                [
                    'class' => 'codemix\streamlog\Target',
                    'url' => 'file:///tmp/yii-stdout',
                    'logVars' => [],
                ],
            ],
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => self::env('SMTP_HOST'),
                'username' => self::env('SMTP_USER'),
                'password' => self::env('SMTP_PASSWORD'),
                'port' => self::env('SMTP_PORT', 25),
                'encryption' => self::env('SMTP_ENCRYPTION', null),
            ],
        ],
        'request' => [
            'cookieValidationKey' => self::env('COOKIE_VALIDATION_KEY', null, !YII_ENV_TEST),
            'trustedHosts' => explode(',', self::env('PROXY_HOST', '192.168.0.0/24')),
        ],
        'session' => [
            'name' => 'MYAPPSID',
            'savePath' => '@app/var/sessions',
            'timeout' => 1440,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['user/login'],
        ],
    ],
    'params' => [
        'mail.from' => ['no-reply@example.com' => 'My Application'],
        'mail.catchAll' => self::env('MAIL_CATCHALL', null),

        'user.passwordResetTokenExpire' => 3600,
        'user.emailConfirmationTokenExpire' => 43200, // 5 days
    ],
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*'],
    ];
}

return $config;
