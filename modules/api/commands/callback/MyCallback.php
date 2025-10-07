<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\CommandInterface;

class MyCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $telegram = \Yii::$app->telegram;
        $query = $telegram->input->callback_query;
        $user = \app\models\User::findByChatId($query->from["id"]);
        $keyboard = [];
        if ($user->can("director")) {
            $text = \Yii::t("telegram", 'command_orders_my');
            foreach (\app\models\Order::findAll(["created_by" => $user->id]) as $order) {
                $keyboard[] = [[ 'text' => !empty($order->summary) ? "#{$order->id} " . $order->summary : \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/order_detail mode=my&id=' . $order->id ]];
            }
        } else if ($user->can("employee")) {
            foreach ($user->orders as $order) {
                $keyboard[] = [[ 'text' => !empty($order->summary) ? "#{$order->id} " . $order->summary : \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/order_detail mode=my&id=' . $order->id ]];
            }
            $text = (empty($keyboard)) ? \Yii::t('telegram', 'command_empty') : \Yii::t('telegram', 'command_order_list');
        }
        $keyboard[] = [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => '/menu']];

        $telegram->editMessageText([
            'chat_id' => $query->from['id'],
            'message_id' => $query->message['message_id'],
            'text' => $text,
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard
            ])
        ]);
    }
}