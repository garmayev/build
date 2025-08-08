<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\CommandInterface;

class OrderCallback extends BaseCallback implements CommandInterface
{
    public function handle($telegram, $args)
    {
        $query = $telegram->input->callback_query;
        parse_str($args[0] ?? '', $data);
        $id = $data["id"] ?? null;

        \Yii::$app->session->set('order_id', $id);

        $telegram->sendMessage([
            'chat_id' => $query->from['id'],
            'message_id' => $query->message['message_id'],
            'text' => \Yii::t('app', 'command_location'),
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => \Yii::t('app', 'command_send_location'), 'request_location' => true]]
                ],
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
            ])
        ]);
        return null;
    }
}