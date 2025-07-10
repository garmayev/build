<?php

namespace app\modules\notifications;

/**
 * @property array $telegramConfig
 * @property array $fcmConfig
 * @property array $apnConfig
 * @property string $controllerNamespace
 */
class Module extends \yii\gii\Module
{
    public $controllerNamespace = 'app\modules\notifications\controllers';
    public $telegramConfig = [
        'botToken' => 'YOUR_BOT_TOKEN',
        'webhookUrl' => 'https://YOUR_WEBHOOK_URL/notifications/webhook',
    ];
    public $fcmConfig = [
        'apiKey' => 'YOUR_FCM_API_KEY',
        'senderId' => 'YOUR_FCM_SENDER_ID',
    ];
    public $apnConfig = [
        'certificatePath' => '@app/certificates/apns.pem',
        'passphrase' => 'YOUR_PASSPHRASE',
        'environment' => 'production',
    ];
    public $handlers = [];

    public function init()
    {
        parent::init();
        $this->registerDependencies();
        $this->registerAliases();
    }

    protected function registerDependencies()
    {
        \Yii::$container->setSingleton('app\modules\notifications\services\TelegramService', function ($container, $params, $config) {
            return new services\TelegramService($this->telegramConfig);
        });
        \Yii::$container->setSingleton('app\modules\notifications\services\FcmService', function () {
            return new services\FcmService($this->fcmConfig);
        });
        \Yii::$container->setSingleton('app\modules\notifications\services\ApnService', function () {
            return new services\ApnService($this->apnConfig);
        });
        \Yii::$container->setSingleton('app\modules\notifications\services\NotificationService', [
            'class' => 'app\modules\notifications\services\NotificationService',
            'service' => [
                'telegram' => \Yii::$container->get('app\modules\notifications\services\TelegramService'),
                'fcm' => \Yii::$container->get('app\modules\notifications\services\FcmService'),
                'apn' => \Yii::$container->get('app\modules\notifications\services\ApnService'),
            ]
        ]);
    }

    protected function registerAliases()
    {
        \Yii::$container->setSingleton('telegram', [
            'class' => 'app\modules\notifications\services\TelegramService',
            'config' => $this->telegramConfig,
        ]);
    }

    protected function registerHandlers()
    {

    }
}