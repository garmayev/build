<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\CommandInterface;

class OrderListCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $query = $telegram->input->callback_query;
        $user = \app\models\User::findByChatId($query->from['id']);

        $keyboard = [];
        $orders = $user->getSuitableOrders()->all();
        foreach ($orders as $order) {
            $keyboard[] = [['text' => !empty($order->summary) ? "#{$order->id} " . $order->summary : \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/order_detail mode=list&id=' . $order->id]];
        };
        $keyboard[] = [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => '/menu']];
        $telegram->editMessageText([
            'chat_id' => $query->from['id'],
            'message_id' => $query->message['message_id'],
            'text' => count($orders) ? \Yii::t('telegram', 'command_order_list') : \Yii::t('telegram', 'command_empty'),
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
        ]);
    }
}