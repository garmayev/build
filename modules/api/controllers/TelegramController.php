<?php

namespace app\modules\api\controllers;

use app\modules\api\commands\callback\ViewOrderCallback;
use app\modules\api\commands\Command;
use app\modules\api\commands\command\MyCommand;
use app\modules\api\commands\command\OrderListCommand;
use app\modules\api\commands\command\StartDayCommand;
use app\modules\api\commands\ContactHandler;
use app\modules\api\commands\LocationHandler;
use app\modules\api\commands\command\MenuCommand;
use app\modules\api\commands\command\StartCommand;
use app\modules\api\commands\callback\OrderCallback;
use yii\base\InvalidConfigException;

class TelegramController extends \yii\web\Controller
{
    public function beforeAction($action)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * @throws InvalidConfigException
     */
    public function actionCallback()
    {
        $telegram = \Yii::$app->telegram;
        Command::onContact(ContactHandler::class);
        Command::onLocation(LocationHandler::class);

        Command::onMessage('/start', StartCommand::class);
        Command::onMessage('/start_day', StartDayCommand::class);
        Command::onMessage('/menu', MenuCommand::class);
        Command::onMessage('/my', MyCommand::class);
        Command::onMessage('/order_list', OrderListCommand::class);

        Command::onCallback('/order', OrderCallback::class);
        Command::onCallback('/view_order', ViewOrderCallback::class);
    }

    private function getProfile($data)
    {
        $phone = preg_replace("/[\(\)\+\ \-]/", "", $data["phone_number"]);
        $profile = \app\models\Profile::findOne(["phone" => $phone]);
        return $profile;
    }
}
