<?php
/**
 * This is the configuration for the Yii app during acceptance tests
 */
return \DockerEnv::webConfig(['/var/www/html/tests/codeception/config/config.php'], [
    'components' => [
        'request' => [
            'cookieValidationKey' => 'TESTING---TESTING',
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
    ],
]);
