<?php
namespace app\modules\api\commands\callback;

use app\modules\api\commands\callback\BaseCallback;
use app\modules\api\commands\CommandInterface;

class OrderRejectCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $query = $telegram->input->callback_query;

        $args = explode(" ", $query->data);
        parse_str($args[1] ?? '', $data);

        $id = $data['id'] ?? null;
        $order = \app\models\Order::findOne($id);
        $user = \app\models\User::findByChatId($query->from['id']);
        if ( $order->revokeCoworker($user) ) {
            $telegram->editMessageText([
                'chat_id' => $query->from['id'],
                'message_id' => $query->message['message_id'],
                'text' => \Yii::t('telegram', 'command_reject_successfully'),
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => '/menu']],
                    ]
                ])
            ]);
        } else {
            $telegram->editMessageText([
                'chat_id' => $query->from['id'],
                'message_id' => $query->message['message_id'],
                'text' => \Yii::t('telegram', 'command_reject_failed'),
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => '/order_detail id='.$id]],
                    ]
                ])
            ]);
        }
    }
}