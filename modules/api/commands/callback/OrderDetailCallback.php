<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\callback\BaseCallback;
use app\modules\api\commands\CommandInterface;

class OrderDetailCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $query = $telegram->input->callback_query;
        $args = explode(" ", $query->data);
        parse_str($args[1] ?? '', $data);
        $user = \app\models\User::findByChatId($query->from['id']);
        $keyboard = [];

        $id = $data['id'] ?? null;
        $mode = $data['mode'];
        if (empty($id) || empty($mode)) {
            return null;
        }

        $order = \app\models\Order::findOne($id);

        if ($user->can('director')) {
            $keyboard = [
                [['text' => \Yii::t('telegram', 'command_send_messages'), 'callback_data' => '/send-messages']],
                [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => $mode === 'my' ? '/my' : '/order_list']],
            ];
        } else if ($user->can('employee')) {
            $hour = \app\models\Hours::find()
                ->where(['date' => date('Y-m-d')])
                ->andWhere(['user_id' => $user->id])
                ->andWhere(['order_id' => $order->id])
                ->one();
            if (isset($hour)) {
                $keyboard = [
                    [['text' => \Yii::t('telegram', 'command_stop_day'), 'callback_data' => '/inline_stop_day id='.$order->id]],
                ];
            } else {
                $keyboard = [
                    [['text' => \Yii::t('telegram', 'command_start_day'), 'callback_data' => '/order id='.$order->id]],
                ];
            }
            $keyboard[] = [['text' => $mode === "my" ? \Yii::t('telegram', 'button_reject') : \Yii::t('telegram', 'button_accept'), 'callback_data' => $mode === "my" ? '/order_reject id='.$id : '/order_accept id='.$id]];
            $keyboard[] = [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => $mode === 'my' ? '/my' : '/order_list']];
        }
        $telegram->editMessageText([
            'chat_id' => $query->from['id'],
            'message_id' => $query->message['message_id'],
            'text' => \app\components\Helper::orderDetails($order),
            'parse_mode' => 'html',
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard
            ])
        ]);
    }
}