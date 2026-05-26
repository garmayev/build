<?php

namespace app\modules\api\commands\telegram\callback;

use app\modules\api\commands\telegram\CommandInterface;
use yii\db\Exception;

class DeclineCallback extends BaseCallback implements CommandInterface
{

    /**
     * @throws Exception
     */
    public function handle($telegram, $args)
    {
        $query = $telegram->input->callback_query;
        parse_str($args[0] ?? '', $data);
        $orderId = $data["order_id"] ?? null;
        $user = \app\models\User::find()
            ->joinWith('profile')
            ->where(['profile.chat_id' => $query->from['id']])
            ->one();

        $order = \app\models\Order::findOne($orderId ?? null);

        $messageId = $query->message['message_id'];
        $chatId = $query->from['id'];

        if ($order->revokeCoworker($user)) {
            \Yii::error("Coworker is revoked {$chatId} successfully");
        } else {
            \Yii::error("Coworker is already removed {$chatId}");
        }

        $message = \app\models\telegram\TelegramMessage::findOne([
            'message_id' => $messageId,
            'chat_id' => $chatId,
            'order_id' => $order->id
        ]);

        if (!empty($message->joined)) {
            $ids = explode(',',$message->joined);
            if (count($ids)) {
                foreach ($ids as $id) {
                    \Yii::error(['chat_id' => $chatId, 'message_id' => $id]);
                    $telegram->deleteMessage([
                        'chat_id' => $chatId,
                        'message_id' => $id,
                    ]);
                }
            }
            $telegram->deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $message->message_id,
            ]);
            $message->delete();
        } else {
            $telegram->deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);
        }
    }
}