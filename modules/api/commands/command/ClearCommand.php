<?php

namespace app\modules\api\commands\command;

use app\modules\api\commands\CommandInterface;
use app\modules\api\commands\Command;

class ClearCommand extends BaseCommand implements CommandInterface
{
    public function handle($telegram, $args)
    {
        $message = $telegram->input->message;
        $chatId = $message->from->id;

        Command::saveResponse($chatId, 'name', null);

        $telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'All data cleaned',
        ]);
    }
}