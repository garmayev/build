<?php

namespace app\modules\api\commands\command;

use app\modules\api\commands\CommandInterface;

class StartCommand extends BaseCommand implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $message = $telegram->input->message;
        $chatId = $message->from->id;
        $profile = \app\models\User::findByChatId($message->from->id);

        if ($profile) {
//            \Yii::error($telegram->botToken);
            $telegram->sendMessage([
                'chat_id' => $message->from->id,
                'text' => \Yii::t('telegram', 'command_already_registered'),
                'reply_markup' => json_encode([
                    'keyboard' => [
                        [['text' => \Yii::t('telegram', 'command_contact'), 'request_contact' => true]],
                        [['text' => \Yii::t('telegram', 'command_location_send'), 'request_location' => true]],
                    ],
                    'one_time_keyboard' => true,
                    'resize_keyboard' => true,
                ])
            ]);
        } else {
            $telegram->sendMessage([
                'chat_id' => $message->from->id,
                'text' => \Yii::t('telegram', 'command_start'),
                'reply_markup' => json_encode([
                    'keyboard' => [
                        [['text' => \Yii::t('telegram', 'command_contact'), 'request_contact' => true]],
                        [['text' => \Yii::t('telegram', 'command_location_send'), 'request_location' => true]],
                    ],
                    'one_time_keyboard' => true,
                    'resize_keyboard' => true,
                ])
            ]);
        }
    }
}