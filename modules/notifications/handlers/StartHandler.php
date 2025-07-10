<?php

namespace app\modules\notifications\handlers;

class StartHandler implements Handler
{

    public static function run(array $args): bool
    {
        \Yii::$app->notificationService->send(
            $args['chat_id'],
            'Заголовок',
            'Сообщение',
            ['telegram'],
            [
                [
                    'name' => 'request_contact',
                    'title' => \Yii::t('app', 'Accept'),
                    'data' => ['message_id' => 1],
                ], [
                    'name' => 'decline',
                    'title' => \Yii::t('app', 'Decline'),
                    'data' => ['message_id' => 1],
                ]
            ]
        );
        return true;
    }
}