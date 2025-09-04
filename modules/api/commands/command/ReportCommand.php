<?php

namespace app\modules\api\commands\command;

use app\modules\api\commands\Command;
use app\modules\api\commands\CommandInterface;

class ReportCommand extends Command implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $message = $telegram->input->message;
        $telegram->sendMessage([
            'chat_id' => $message->chat->id,
            'text' => \Yii::t('telegram', 'message_add_report')
        ]);
    }
}