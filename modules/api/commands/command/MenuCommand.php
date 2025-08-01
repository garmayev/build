<?php

namespace app\modules\api\commands\command;

use app\modules\api\commands\CommandInterface;

class MenuCommand extends BaseCommand implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $message = $telegram->input->message;
        $telegram->sendMessage([
            'chat_id' => $message->from->id,
            'text' => \Yii::t('app', 'command_menu'),
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => \Yii::t('app', 'command_start_day'), 'callback_data' => '/start_day']],
                    [['text' => \Yii::t('app', 'command_orders_list'), 'callback_data' => '/order_list']],
                    [['text' => \Yii::t('app', 'command_orders_my'), 'callback_data' => '/my']]
                ]
            ])
        ]);
    }
}