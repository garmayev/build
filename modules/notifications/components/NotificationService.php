<?php

namespace app\modules\notifications\components;

use app\modules\notifications\models\Notification;
use yii\base\Component;

class NotificationService extends Component
{
    public $handlers = [];
    private $actions = [];

    public function registerHandler(string $actionName, callable $handler)
    {
        $this->handlers[$actionName] = $handler;
    }

    public function actionHandler(string $actionName, array $params)
    {
        if (isset($this->handlers[$actionName])) {
            return call_user_func($this->handlers[$actionName], $params);
        }

        $telegramConfig = \Yii::$app->getModule('notifications')->telegramConfig;
        if (isset($telegramConfig['handlerMap'][$actionName])) {
            $handlerClass = $telegramConfig['handlerMap'][$actionName];
            if (class_exists($handlerClass)) {
                return $handlerClass::run($params);
            }
        }

        \Yii::warning(\Yii::t('app', 'Handler "{actionName}" not exists', ['{actionName}' => $actionName]));
        return false;

    }

    public function send($recipient, string $title, string $message, array $channels, $actions = [])
    {
        if (is_array($recipient)) {
            $results = [];
            foreach ($recipient as $rec) {
                $results[$rec] = $this->sendSingle($rec, $title, $message, $channels, $actions);
            }
            return $results;
        }
        return $this->sendSingle($recipient, $title, $message, $channels, $actions);
    }

    private function sendSingle($recipient, string $title, string $message, array $channels, array $actions = [])
    {
        $notification = new Notification();
        $notification->recipient = $recipient;
        $notification->title = $title;
        $notification->message = $message;
        $notification->channels = $channels;
        $notification->actions = $actions;

        if (!$notification->validate()) {
            \Yii::error(\Yii::t('app', 'Notification validation failed: ') . print_r($notification->errors, true));
            return [
                'success' => false,
                'errors' => $notification->errors
            ];
        }

        try {
            $service = \Yii::$container->get(\app\modules\notifications\services\NotificationService::class);
            return $service->send($notification);
        } catch (\Exception $exception) {
            \Yii::error(\Yii::t('app', 'Notification service failed: ') . print_r($exception, true));
            return [
                'success' => false,
                'errors' => $exception->getMessage()
            ];
        }
    }
}