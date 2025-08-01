<?php

namespace app\modules\api\commands\command;

use app\modules\api\commands\CommandInterface;

class StartDayCommand extends BaseCommand implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $message = $telegram->input->message;
        $user = \app\models\User::findByChatId($message->from->id);
        $keyboard = [];
        $hour = \app\models\Hours::find()
            ->where(['user_id' => $user->id])
            ->andWhere(['date' => \Yii::$app->formatter->asDate(time(), 'php:Y-m-d')])
            ->one();
        if (empty($hour)) {
            foreach ($user->orders as $order) {
                $keyboard[] = [['text' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/order id=' . $order->id]];
            }
            $telegram->sendMessage([
                'chat_id' => $this->message->from->id,
                'text' => (empty($keyboard)) ? \Yii::t('app', 'command_empty') : \Yii::t('app', 'command_order_list'),
                'reply_markup' => json_encode([
                    'inline_keyboard' => $keyboard,
                    'one_time_keyboard' => true,
                    'resize_keyboard' => true,
                ])
            ]);
        } else {
            $telegram->sendMessage([
                'chat_id' => $this->message->from->id,
                'text' => \Yii::t('app', 'command_hours_isset')
            ]);
        }
    }
}