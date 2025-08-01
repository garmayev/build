<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\CommandInterface;

class OrderCallback extends BaseCallback implements CommandInterface
{
    public function handle($telegram, $args)
    {
        parse_str($args[0] ?? '', $data);
        $id = $data["id"] ?? null;
        if (empty($id)) {
//            $telegram->answerCallbackQuery([
//                'callback_query_id' => $this->query->id,
//            ]);
        }
        \Yii::$app->session->set('order_id', $id);
        $telegram->answerCallbackQuery([
            'chat_id' => $telegram->input->callback_query->from['id'],
            'text' => \Yii::t('app', 'command_location'),
            'reply_markup' => json_encode([
                'keyboard' => [
                    [
                        ['text' => \Yii::t('app', 'command_send_location'), 'request_location' => true]
                    ]
                ],
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
            ])
        ]);
        return null;
    }
}