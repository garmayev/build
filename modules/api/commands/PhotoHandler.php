<?php

namespace app\modules\api\commands;

use app\modules\api\commands\command\BaseCommand;
use app\modules\api\commands\CommandInterface;

class PhotoHandler extends command\BaseCommand implements CommandInterface
{
    public function handle($telegram, $args)
    {
        $message = $telegram->input->message;
        $photos = $message->photos;
        foreach ($photos as $photo) {
            \Yii::error($photo);
        }
    }
}