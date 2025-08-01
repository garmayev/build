<?php

namespace app\modules\api\commands\command;

use app\modules\api\commands\CommandInterface;

class StartCommand extends BaseCommand implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $chatId = $telegram->input->message->from->id;
        $profile = \app\models\User::find()->joinWith('profile')->where(['profile.chat_id' => $chatId])->one();

        if ($profile) {
            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => \Yii::t('telegram', 'command_already_registered')
            ]);
        } else {
            $telegram->sendMessage([
                'chat_id' => $telegram->input->message->from->id,
                'text' => \Yii::t('telegram', 'command_start'),
                'reply_markup' => json_encode([
                    'keyboard' => [
                        [['text' => \Yii::t('telegram', 'command_contact'), 'request_contact' => true]],
                    ],
                    'one_time_keyboard' => true,
                    'resize_keyboard' => true,
                ])
            ]);
        }
    }
}