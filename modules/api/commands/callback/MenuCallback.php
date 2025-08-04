<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\CommandInterface;

class MenuCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $telegram = \Yii::$app->telegram;
        $query = $telegram->input->callback_query;

        $telegram->editMessageText([
            'chat_id' => $query->from['id'],
            'message_id' => $query->message['message_id'],
            'text' => \Yii::t('telegram', 'message_menu'),
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => \Yii::t('telegram', 'command_start_day'), 'callback_data' => '/inline_start_day']],
                    [['text' => \Yii::t('telegram', 'command_orders_list'), 'callback_data' => '/order_list']],
                    [['text' => \Yii::t('telegram', 'command_orders_my'), 'callback_data' => '/my']]
                ]
            ])
        ]);
    }
}