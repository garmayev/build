<?php

namespace app\modules\api\commands\telegram\command;

use app\modules\api\commands\telegram\Command;
use app\modules\api\commands\telegram\CommandInterface;

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