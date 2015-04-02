<?php
/**
 * This is the configuration for the Yii app during functional tests
 */

$_SERVER['SCRIPT_FILENAME'] = YII_TEST_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = YII_TEST_ENTRY_URL;
return \DockerEnv::webConfig(['/var/www/html/tests/codeception/config/config.php'], [
    'components' => [
        'request' => [
            // Problematic with functional tests
            'enableCsrfValidation' => false,
            /*
            // If really required, this workaround can help:
            'csrfCookie' => [
                'domain' => 'localhost',
            ],
            */
            'cookieValidationKey' => 'TESTING---TESTING',
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
    ],
]);
