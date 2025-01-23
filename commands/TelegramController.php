<?php
namespace app\commands;

use yii\console\Controller;
use yii\helpers\Url;

class TelegramController extends Controller 
{
    private $curl;
    private $bot_id;

    public function init()
    {
//        \Yii::error( \Yii::$app->telegram );
        parent::init();
    }

    public function actionCommands()
    {
        $data = ["commands" => json_encode([
            [
                "command" => "/check",
                "description" => \Yii::t("app", "Test My Command"),
            ], [
                "command" => "login",
                "description" => \Yii::t("app", "Login to system"),
            ],
        ])];

        \Yii::error(\app\models\telegram\Telegram::send("setMyCommands", $data));
    }
}