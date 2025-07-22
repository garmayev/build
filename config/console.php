<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'language' => 'ru-RU',
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ],
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'telegram' => [
            'class' => \aki\telegram\Telegram::class,
            'botToken' => '922790224:AAHG6WJNmj8-0qmjOYZAeNL3Ag0nNPT8rcE',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'enabled' => true,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'baseUrl' => 'https://build.amgcompany.ru/',
            'hostInfo' => 'https://build.amgcompany.ru',
            'scriptUrl' => 'https://build.amgcompany.ru',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'db' => $db,
        'notificationService' => [
            'class' => \app\modules\notifications\components\NotificationService::class,
        ],
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => [
                '@app/migrations',
                '@yii/rbac/migrations',
                '@app/modules/notifications/migrations',
            ],
        ]
    ],
    'modules' => [
        'notifications' => [
            'class' => \app\modules\notifications\Module::class,
            'telegramConfig' => [
                'botToken' => '922790224:AAHG6WJNmj8-0qmjOYZAeNL3Ag0nNPT8rcE',
                'webhookUrl' => '',
                'commandMap' => [
                    'start' => \app\modules\notifications\handlers\StartHandler::class,
                    'accept' => \app\modules\notifications\handlers\AcceptHandler::class,
                    'decline' => \app\modules\notifications\handlers\DeclineHandler::class,
                ],
            ],
            'fcmConfig' => [
                'apiKey' => '1:523783308536:android:bf22707b654015434aa70d',
                'senderId' => '523783308536'
            ],
            'apnConfig' => [
                'passphrase' => '',
                'environment' => YII_ENV_DEV ? 'development' : 'production',
            ],
        ]
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
