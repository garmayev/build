<?php

namespace app\modules\api\commands;

use app\modules\api\commands\command\BaseCommand;
use app\modules\api\commands\CommandInterface;

class PhotoHandler extends command\BaseCommand implements CommandInterface
{
    public function handle($telegram, $args)
    {
        $message = $telegram->input->message;
        foreach ($message->photo as $photo) {
            \Yii::error("https://api.telegram.org/file/bot{$telegram->botToken}/photo/{$photo->photo_id}/");
            \Yii::error($photo);
        }
    }
}