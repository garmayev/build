<?php

namespace app\modules\api\commands\command;

use yii\base\BaseObject;

class BaseCommand extends BaseObject
{
    private $message;
    public function __construct($config = [])
    {
        session_id(\Yii::$app->telegram->input->message->from->id);
        $this->message = \Yii::$app->telegram->input->message;
        parent::__construct($config);
    }
}