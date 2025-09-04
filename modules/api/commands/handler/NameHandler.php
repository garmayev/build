<?php

namespace app\modules\api\commands\handler;

use app\modules\api\commands\Command;

class NameHandler {
    public function handle($telegram, $name)
    {
        $message = $telegram->input->message;
        $chatId = $message->chat->id;

        \Yii::error($name);

        Command::saveResponse($chatId, 'name', $name);

        $telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Имя сохранено: {$name}"
        ]);
    }
}