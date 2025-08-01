<?php

namespace app\modules\api\controllers;

use app\modules\api\commands\callback\AcceptCallback;
use app\modules\api\commands\callback\DeclineCallback;
use app\modules\api\commands\callback\OrderDetailCallback;
use app\modules\api\commands\callback\OrderViewCallback;
use app\modules\api\commands\callback\StartDayCallback;
use app\modules\api\commands\Command;
use app\modules\api\commands\command\MyCommand;
use app\modules\api\commands\command\OrderListCommand;
use app\modules\api\commands\command\StartDayCommand;
use app\modules\api\commands\ContactHandler;
use app\modules\api\commands\LocationHandler;
use app\modules\api\commands\command\MenuCommand;
use app\modules\api\commands\command\StartCommand;
use app\modules\api\commands\callback\OrderCallback;
use app\modules\api\commands\callback\MyCallback;
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
        Command::onCallback('/my', MyCallback::class);
        Command::onCallback('/order_detail', OrderDetailCallback::class);
        Command::onCallback('/order_view', OrderViewCallback::class);
        Command::onCallback('/order_list', OrderListCommand::class);
        Command::onCallback('/accept', AcceptCallback::class);
        Command::onCallback('/decline', DeclineCallback::class);
        Command::onCallback('/start_day', StartDayCallback::class);
    }

    private function getProfile($data)
    {
        $phone = preg_replace("/[\(\)\+\ \-]/", "", $data["phone_number"]);
        $profile = \app\models\Profile::findOne(["phone" => $phone]);
        return $profile;
    }
}
