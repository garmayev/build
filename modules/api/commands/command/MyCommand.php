<?php

namespace app\modules\api\commands\command;

use app\modules\api\commands\CommandInterface;

class MyCommand extends BaseCommand implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $user = \app\models\User::find()
            ->joinWith('profile')
            ->where(['profile.chat_id' => $this->message->from->id])
            ->one();
        $keyboard = [];
        foreach ($user->orders as $order) {
            $keyboard[] = [['text' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/view_order id=' . $order->id]];
        }
        $telegram->sendMessage([
            'chat_id' => $this->message->from->id,
            'text' => (empty($keyboard)) ? \Yii::t('telegram', 'command_empty') : \Yii::t('telegram', 'command_order_list'),
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard
            ])
        ]);
    }
}