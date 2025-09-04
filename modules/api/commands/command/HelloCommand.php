<?php

namespace app\modules\api\commands\command;

use app\modules\api\commands\CommandInterface;
use app\modules\api\commands\Command;

class HelloCommand extends BaseCommand implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $message = $telegram->input->message;
        $chatId = $message->from->id;

        $telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => \Yii::t('app', 'send_name'),
        ]);

        Command::expectResponse($chatId, 'waiting_for_name');
    }
}