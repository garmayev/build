<?php

namespace app\modules\api\commands\command;

use app\modules\api\commands\CommandInterface;

class MyCommand extends BaseCommand implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $telegram = \Yii::$app->telegram;
        $message = $telegram->input->message;
        $user = \app\models\User::find()
            ->joinWith('profile')
            ->where(['profile.chat_id' => $message->from->id])
            ->one();
        $keyboard = [];
        foreach ($user->orders as $order) {
            $keyboard[] = [['text' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/view_order id=' . $order->id]];
        }
        $telegram->sendMessage([
            'chat_id' => $message->from->id,
            'text' => (empty($keyboard)) ? \Yii::t('telegram', 'command_empty') : \Yii::t('telegram', 'command_order_list'),
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard
            ])
        ]);
    }
}