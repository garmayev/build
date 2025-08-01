<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\CommandInterface;

class MyCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $user = \app\models\User::findByChatId($this->query->from["id"]);
        $keyboard = [];
        foreach ($user->orders as $order) {
            $keyboard[] = [
                [
                    'text' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]),
                    'callback_data' => '/view_order id=' . $order->id
                ]
            ];
        }
        $text = (empty($keyboard)) ? \Yii::t('app', 'command_empty') : \Yii::t('app', 'command_order_list');
        $telegram->answerCallbackQuery([
            'chat_id' => $this->query->from['id'],
            'message_id' => $this->query->message['message_id'],
            'text' => $text,
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard
            ])
        ]);
    }
}