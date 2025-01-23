<?php
namespace app\modules\messenger;

use \yii\base\Module;

class MessengerModule extends Module 
{
    public $controllerNamespace = "app\modules\messenger\controllers";
    public $telegram_bot_id = "";
    public $use_database = "";

    public function init()
    {
        parent::init();
    }
}