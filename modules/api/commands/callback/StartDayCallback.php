<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\callback\BaseCallback;
use app\modules\api\commands\CommandInterface;

class StartDayCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $query = $telegram->input->callback_query;
        $user = \app\models\User::findByChatId($query->from['id']);
        $keyboard = [];
        $hour = \app\models\Hours::find()
            ->where(['user_id' => $user->id])
            ->andWhere(['date' => \Yii::$app->formatter->asDate(time(), 'php:Y-m-d')])
            ->all();

        foreach ($user->getOrders()->andWhere(['not', ['order.id' => \yii\helpers\ArrayHelper::getColumn($hour, 'order_id')]])->all() as $order) {
            $keyboard[] = [['text' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/order id=' . $order->id]];
        }
        $keyboard[] = [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => '/menu']];
        $telegram->editMessageText([
            'chat_id' => $query->from['id'],
            'message_id' => $query->message['message_id'],
            'text' => (empty($keyboard)) ? \Yii::t('app', 'command_empty') : \Yii::t('app', 'command_order_list'),
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
            ])
        ]);
    }
}