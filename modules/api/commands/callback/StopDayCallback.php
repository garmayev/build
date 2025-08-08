<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\callback\BaseCallback;
use app\modules\api\commands\CommandInterface;

class StopDayCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $query = $telegram->input->callback_query;
        $user = \app\models\User::findByChatId($query->from['id']);
        $keyboard = [];
        $hour = \app\models\Hours::find()
            ->where(['user_id' => $user->id])
            ->andWhere(['date' => \Yii::$app->formatter->asDate(time(), 'php:Y-m-d')])
            ->andWhere(['not', ['start_time' => null]])
            ->one();

//        \Yii::error($hour);
        if (empty($hour)) {
            $keyboard[] = [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => '/menu']];
            $telegram->editMessageText([
                'chat_id' => $query->from['id'],
                'message_id' => $query->message['message_id'],
                'text' => (count($keyboard) === 1) ? \Yii::t('app', 'command_empty') : \Yii::t('app', 'command_order_list'),
                'reply_markup' => json_encode([
                    'inline_keyboard' => $keyboard,
                    'one_time_keyboard' => true,
                    'resize_keyboard' => true,
                ])
            ]);
        } else {
            $hour->stop_time = \Yii::$app->formatter->asDatetime(time(), 'php:Y-m-d H:i:s');
            $hour->count = ceil((\Yii::$app->formatter->asTimestamp($hour->stop_time) - \Yii::$app->formatter->asTimestamp($hour->start_time)) / 3600);
            if ($hour->save()) {
                $telegram->editMessageText([
                    'chat_id' => $query->from['id'],
                    'message_id' => $query->message['message_id'],
                    'text' => \Yii::t('telegram', 'command_hours_closed')."\n".\Yii::t('telegram', 'command_hours_worked_{time}', ['time' => $hour->count]),
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => '/menu']]
                        ]
                    ])
                ]);
            } else {
                $telegram->editMessageText([
                    'chat_id' => $query->from['id'],
                    'message_id' => $query->message['message_id'],
                    'text' => \Yii::t('telegram', 'command_hours_error')."\n".\Yii::t('telegram', 'command_please_try_later'),
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => '/menu']]
                        ]
                    ])
                ]);

            }
        }
    }
}