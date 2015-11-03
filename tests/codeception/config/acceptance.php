<?php
/**
 * This is the configuration for the Yii app during acceptance tests
 */
return yii\helpers\ArrayHelper::merge(
    require('/var/www/html/config/web.php'),
    require('/var/www/html/tests/codeception/config/config.php'),
    [
        'components' => [
            'request' => [
                'cookieValidationKey' => 'TESTING---TESTING',
            ],
            'urlManager' => [
                'showScriptName' => true,
            ],
        ],
    ]
);
