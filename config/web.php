<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'ru-RU',
    'timeZone' => 'Asia/Irkutsk',
    'name' => 'Стройка',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'ozwR_KOqraPzu1M3-F3MUZ8FTUzr1mYY',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'session' => [
            'name' => 'build',
//            'savePath' => '../runtime/sessions',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['user/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => false,
            'transport' => [
                'scheme' => 'smtps',
                'host' => 'smtp.mail.ru',
                'username' => 'amg.company@inbox.ru',
                'password' => 'dAV5dj7Y28JrBThXmNWk',
                'ssl' => true,
                'port' => 465,
//                'dsn' => 'smtp://buryatagro:motmtimsudchctinh@smtp.yandex.ru:465'
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'enabled' => true,
                    'enableRotation' => true,
                    'levels' => ['error', 'warning'],
                    'logFile' => '@runtime/logs/app.log',
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'defaultRoles' => ['guest'],
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ],
                'telegram*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'fileMap' => [
                        'telegram' => 'telegram.php'
                    ]
                ]
            ],
        ],
        'telegram' => [
            'class' => \aki\telegram\Telegram::class,
            'botToken' => '8461352654:AAGxgiJVcy2ScgSO6p5akN4gzzSEC25ZlQM',
        ],
        'notificationService' => [
            'class' => \app\modules\notifications\components\NotificationService::class,
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                $response->getHeaders()->set('Access-Control-Allow-Origin', '*');
                $response->getHeaders()->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
                $response->getHeaders()->set('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
            },
        ],
    ],
    'modules' => [
        'api' => [
            'class' => \app\modules\api\ApiModule::class,
        ],
        'messenger' => [
            'class' => \app\modules\messenger\MessengerModule::class,
            'telegram_bot_id' => '922790224:AAHG6WJNmj8-0qmjOYZAeNL3Ag0nNPT8rcE',
            'use_database' => true,
        ],
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

//if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
//}

return $config;
