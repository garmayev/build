<?php

namespace app\commands;

use app\components\Helper;
use app\models\Config;
use app\models\Coworker;
use app\models\Order;
use app\models\OrderCoworker;
use app\models\User;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

class OrderController extends Controller
{
    public function actionStack($order_id)
    {
        $order = Order::findOne($order_id);
        echo $order->isFull();
    }

    public function actionNotify($order_id = null, $priority = User::PRIORITY_HIGH)
    {
        session_start();
        $models = Order::find()->where(['status' => Order::STATUS_NEW])->all();
        echo "[" . \Yii::$app->formatter->asDatetime(time(), "php:Y-m-d H:i:s") . "]\n";
        foreach ($models as $model) {
            $priority = $this->getPriority($model, isset($model->priority_level) ? $model->priority_level - 1 : User::PRIORITY_HIGH);
            $_SESSION['__id'] = $model->created_by;
            if ($model->priority_level >= 0) {
                echo "Order #{$model->id}\n";
                if ($this->checkTime($model->notify_date, $model)) {
                    echo "\tOrder {$model->id} is needle to notify\n";
                    echo "\tPriority: $priority\n";
                    $model->priority_level = $model->priority_level--;
                    $model->notify_date = time();
                    $model->save();
                    $model->sendAndUpdateTelegramNotifications();
                }
            }
        }
    }

    private function checkTime($timestamp, Order $model): bool
    {
        $now = time();
        $delay = Config::findOne(["name" => "priority_level_{$model->priority_level}"]);
        $delay_seconds = Helper::timeToSeconds($delay->value . ":00");
        echo "\tDelay in seconds: {$delay_seconds}\n";
        $elapsed = $now - $timestamp;
        echo "\tElapsed time: {$elapsed}\n";
        return ($elapsed - $delay_seconds > 0);
    }

    private function getPriority(Order $model, $priority)
    {
        for ($i = $priority; $i >= -1; $i--) {
//            echo "#$i ";
            if (count($model->getSuitableCoworkers()) > 0) {
                return $i;
            }
        }
        return false;
    }

    public function actionSuitableCoworkers($id)
    {
        $order = Order::findOne($id);
        $coworkers = $order->getSuitableCoworkers();
        if (count($coworkers) === 0) {
            echo "Empty\n";
        }
        /**
         * @var $coworker User
         */
        foreach ($coworkers as $coworker) {
            echo $coworker->name . "\n";
        }
        echo "Total: " . count($coworkers) . "\n\n";
    }
}