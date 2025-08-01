<?php

namespace app\modules\api\commands\command;

use app\models\User;
use app\modules\api\commands\CommandInterface;

class OrderListCommand extends BaseCommand implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $user = User::findByChatId($telegram->input->message->from->id);
        if (empty($user)) {
            \Yii::error("Unknown user");
            return null;
        }

        $keyboard = [];
        $orders = $user->getSuitableOrders();
        foreach ($orders as $order) {
            $keyboard[] = [['text' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/order_detail id=' . $order->id]];
        }
        $telegram->sendMessage([
            'chat_id' => $telegram->input->message->from->id,
            'text' => count($orders) ? \Yii::t('telegram', 'command_order_list') : \Yii::t('telegram', 'command_empty'),
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
        ]);
    }
}