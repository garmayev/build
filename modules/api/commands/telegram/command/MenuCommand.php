<?php

namespace app\modules\api\commands\telegram\command;

use app\modules\api\commands\telegram\CommandInterface;

class MenuCommand extends BaseCommand implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $message = $telegram->input->message;
        $telegram->sendMessage([
            'chat_id' => $message->from->id,
            'text' => \Yii::t('telegram', 'message_menu'),
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => \Yii::t('telegram', 'command_orders_my'), 'callback_data' => '/my']],
                    [['text' => \Yii::t('telegram', 'command_orders_list'), 'callback_data' => '/order_list']],
                    [['text' => \Yii::t('telegram', 'command_day_list'), 'callback_data' => '/day_list']],
                ]
            ])
        ]);
    }
}