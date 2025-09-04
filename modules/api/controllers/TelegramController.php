<?php

namespace app\modules\api\controllers;

use app\modules\api\commands\callback\AcceptCallback;
use app\modules\api\commands\callback\DayListCallback;
use app\modules\api\commands\callback\DayViewCallback;
use app\modules\api\commands\callback\DayDetailCallback;
use app\modules\api\commands\callback\DeclineCallback;
use app\modules\api\commands\callback\OrderAcceptCallback;
use app\modules\api\commands\callback\OrderDetailCallback;
use app\modules\api\commands\callback\OrderViewCallback;
use app\modules\api\commands\callback\OrderListCallback;
use app\modules\api\commands\callback\OrderStatusProcess;
use app\modules\api\commands\callback\OrderRejectCallback;
use app\modules\api\commands\callback\StartDayCallback;
use app\modules\api\commands\callback\StopDayCallback;
use app\modules\api\commands\Command;
use app\modules\api\commands\command\MyCommand;
use app\modules\api\commands\command\OrderListCommand;
use app\modules\api\commands\command\ReportCommand;
use app\modules\api\commands\command\StartDayCommand;
use app\modules\api\commands\command\DayListCommand;
use app\modules\api\commands\command\HelloCommand;
use app\modules\api\commands\command\ClearCommand;
use app\modules\api\commands\command\ShowDataCommand;
use app\modules\api\commands\handler\NameHandler;
use app\modules\api\commands\ContactHandler;
use app\modules\api\commands\LocationHandler;
use app\modules\api\commands\command\MenuCommand;
use app\modules\api\commands\command\StartCommand;
use app\modules\api\commands\callback\OrderCallback;
use app\modules\api\commands\callback\MyCallback;
use app\modules\api\commands\callback\MyCoworkersCallback;
use app\modules\api\commands\callback\MenuCallback;
use app\modules\api\commands\PhotoHandler;
use yii\base\InvalidConfigException;

class TelegramController extends \yii\web\Controller
{
    public function beforeAction($action)
    {
        if ($action->id !== 'builder') {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $this->enableCsrfValidation = false;
        }
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
        Command::onPhoto(PhotoHandler::class);

        Command::onMessage('waiting_for_name', NameHandler::class, 'name');
        Command::onMessage('/start', StartCommand::class);
        Command::onMessage('/start_day', StartDayCommand::class);
        Command::onMessage('/menu', MenuCommand::class);
        Command::onMessage('/my', MyCommand::class);
        Command::onMessage('/order_list', OrderListCommand::class);
        Command::onMessage('/day_list', DayListCommand::class);
        Command::onMessage('/hello', HelloCommand::class);
        Command::onMessage('/clear', ClearCommand::class);
        Command::onMessage('/show_data', ShowDataCommand::class);
        Command::onMessage('/report', ReportCommand::class);

        $update = $telegram->input;
        if (Command::handleContextResponse($update)) {
            return ;
        }

        Command::onCallback('/order', OrderCallback::class);
        Command::onCallback('/my', MyCallback::class);
        Command::onCallback('/order_detail', OrderDetailCallback::class);
        Command::onCallback('/order_view', OrderViewCallback::class);
        Command::onCallback('/order_list', OrderListCallback::class);
        Command::onCallback('/accept', AcceptCallback::class);
        Command::onCallback('/decline', DeclineCallback::class);
        Command::onCallback('/inline_start_day', StartDayCallback::class);
        Command::onCallback('/inline_stop_day', StopDayCallback::class);
        Command::onCallback('/menu', MenuCallback::class);
        Command::onCallback('/order_reject', OrderRejectCallback::class);
        Command::onCallback('/order_accept', OrderAcceptCallback::class);
        Command::onCallback('/my_coworkers', MyCoworkersCallback::class);
        Command::onCallback('/order_status_process', OrderStatusProcessCallback::class);
        Command::onCallback('/day_list', DayListCallback::class);
        Command::onCallback('/day_view', DayViewCallback::class);
        Command::onCallback('/day_detail', DayDetailCallback::class);
    }

    public function actionBuilder()
    {
        return $this->render('builder');
    }

    private function getProfile($data)
    {
        $phone = preg_replace("/[\(\)\+\ \-]/", "", $data["phone_number"]);
        $profile = \app\models\Profile::findOne(["phone" => $phone]);
        return $profile;
    }

    public function actionCheckChatId($chat_id)
    {
        $user = \app\models\User::findByChatId($chat_id);
        return ["ok" => isset($user), "data" => $user];
    }
}
