<?php
/**
 * This is the configuration for the Yii app shared by all tests
 */
return [
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\faker\FixtureController',
            // Uncomment to generate fixtures in another language
            //'language' => 'de_DE',
            'fixtureDataPath' => '@tests/codeception/fixtures/data',
            'templatePath' => '@tests/codeception/fixtures/templates',
            'namespace' => 'tests\codeception\fixtures',
        ],
    ],
    'components' => [
        'mailer' => [
            'useFileTransport' => true,
        ],
    ],
];
