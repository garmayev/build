<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\callback\BaseCallback;
use app\modules\api\commands\CommandInterface;
use yii\db\Exception;

class SingleDecline extends BaseCallback implements CommandInterface
{

    /**
     * @throws Exception
     */
    public function handle($telegram, $args)
    {
        $query = $telegram->input->callback_query;
        $user = \app\models\User::findByChatId($query->from['id']);
        parse_str($args[0] ?? '', $data);
        $orderId = $data["order_id"] ?? null;
        $mode  = $data["mode"] ?? null;
        $order = \app\models\Order::findOne($orderId);

        if ($order->revokeCoworker($user)) {
            foreach ($order->telegramMessages as $message) {
                $message->editMessageText(\app\components\Helper::generateTelegramMessage($orderId));
            }
        }

        $telegram->editMessageText([
            'chat_id' => $query->from['id'],
            'message_id' => $query->message['message_id'],
            'text' => \Yii::t('telegram', 'command_order_is_decline'),
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => $mode === 'my' ? '/my' : '/order_list']],
                ]
            ])
        ]);
    }
}