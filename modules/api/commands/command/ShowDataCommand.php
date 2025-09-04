<?php

namespace app\modules\api\commands\command;

use app\modules\api\commands\CommandInterface;
use app\modules\api\commands\Command;

class ShowDataCommand extends BaseCommand implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $message = $telegram->input->message;
        $chatId = $message->from->id;

        $name = Command::getResponse($chatId, 'name', 'Не указано');
        $telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Ваши данные:\nИмя: {$name}",
        ]);
    }
}