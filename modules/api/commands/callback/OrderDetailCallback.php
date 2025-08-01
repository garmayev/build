<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\callback\BaseCallback;
use app\modules\api\commands\CommandInterface;

class OrderDetailCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        parse_str($args[0] ?? '', $data);
        $id = $data['id'] ?? null;
        if (empty($id)) {
            return null;
        }

        $order = \app\models\Order::findOne($id);
        $user = \app\models\User::findByChatId($this->query->from['id']);

        $telegram->answerCallbackQuery([
            'callback_query_id' => $this->query->id,
            'chat_id' => $this->query->from['id'],
            'text' => \Yii::t('telegram', 'answer_order_detail_{id}', ['id' => $id]),
        ])->editMessageText([
            'chat_id' => $this->query->from['id'],
            'message_id' => $this->query->message['message_id'],
            'text' => "<b>".\Yii::t("app", "Order #{id}", ['id' => $id])."</b>" . \app\components\Helper::orderDetails($order),
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => \Yii::t('telegram', 'button_reject'), 'callback_data' => '/order_reject id='.$id]],
                    [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => '/order_detail id='.$id]],
                ]
            ])
        ]);
    }
}