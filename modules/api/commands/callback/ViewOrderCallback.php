<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\CommandInterface;

class ViewOrderCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        parse_str($args[0] ?? '', $data);
        $id = $data["id"] ?? null;

        if (empty($id)) {
            return null;
        }

        $telegram->editMessageText([
            'chat_id' => $telegram->input->callback_query->from['id'],
            'parse_mode' => 'html',
            'message_id' => $telegram->input->callback_query->message['message_id'],
            'text' => "<b>" . \Yii::t("app", "Order #{id}", ['id' => $id]) . "</b>\n" . \app\components\Helper::generateTelegramMessage($id),
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => \Yii::t('telegram', 'command_decline'), 'callback_data' => '/decline order_id=' . $id]],
                    [['text' => \Yii::t('telegram', 'command_back'), 'callback_data' => '/my']],
                ]
            ])
        ]);
    }
}