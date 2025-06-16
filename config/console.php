<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'language' => 'ru',
    'bootstrap' => ['log', 'app\modules\user\Bootstrap'],
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
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                    'categories' => ['yii\*'],
                    'except' => [
                        'yii\web\HttpException:404',
                    ],
                ],
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
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => [
                '@yii/rbac/migrations',
                '@app/migrations/',
            ],
        ]
    ],
    'params' => $params,
    'modules' => [
        'user' => [
            'class' => 'app\modules\user\Module',
        ]
    ]
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
