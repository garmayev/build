<?php

namespace app\modules\notifications\services;

use app\modules\notifications\models\Notification;
use app\modules\notifications\models\NotificationLog;

class NotificationService
{
    public $service;
    public function send(Notification $notification)
    {
        $results = [];
        foreach ($notification->channels as $channel) {
            if (isset($this->service[$channel])) {
                try {
                    $result = $this->service[$channel]->send($notification);
                    $results[$channel] = $result;
                } catch (\Exception $exception) {
                    $results[$channel] = [
                        'ok' => false,
                        'error' => $exception->getMessage()
                    ];
                }
            }
        }

        return $results;
    }

    public function handleCallback($channel, $data)
    {
        \Yii::error($channel);
        if (isset($this->service[$channel])) {
            return $this->service[$channel]->handleCallback($data);
        }
        return false;
    }

    protected function logNotification(Notification $notification, $channel, $result)
    {
        $log = new NotificationLog();
        $log->notification_id = $notification->id;
        $log->channel = $channel;
        $log->recipient = $notification->recipient;
        $log->status = $result['ok'] ? NotificationLog::STATUS_SENT : NotificationLog::STATUS_FAILED;
        $log->response = json_encode($result);
        $log->save();
    }
}