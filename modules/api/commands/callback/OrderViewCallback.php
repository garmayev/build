<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\CommandInterface;

class OrderViewCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $query = $telegram->input->callback_query;
        parse_str($args[0] ?? '', $data);
        $id = $data["id"] ?? null;
        $mode = $data["mode"] ?? null;

        $keyboard = [];
        if ($mode === "my") {
            $button = \Yii::t('telegram', 'command_decline');
            $command = "/single_decline";
        } else {
            $button = \Yii::t('telegram', 'command_accept');
            $command = "/single_accept";
        }

        $keyboard = [
            [['text' => $button, 'callback_data' => $command.'&order_id=' . $id]],
            [['text' => \Yii::t('telegram', 'command_back'), 'callback_data' => '/my']],
        ];

        $telegram->editMessageText([
            'chat_id' => $query->from['id'],
            'parse_mode' => 'html',
            'message_id' => $query->message['message_id'],
            'text' => "<b>" . \Yii::t("app", "Order #{id}", ['id' => $id]) . "</b>\n" . \app\components\Helper::generateTelegramMessage($id),
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard
            ])
        ]);
    }
}