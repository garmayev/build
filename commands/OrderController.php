<?php

namespace app\commands;

use app\components\Helper;
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
        if ($order_id) {
            $model = Order::findOne($order_id);
            $_SESSION['__id'] = $model->created_by;
//            echo $model->notify_stage . "\n";
            if ($model->priority_level >= 0) {
                if ($this->checkTime($model->notify_date)) {
                    $model->sendAndUpdateTelegramNotifications($model->priority_level - 1);
                }
            }
        } else {
            $models = Order::find()->where(['status' => Order::STATUS_NEW])->all();
            foreach ($models as $model) {
                echo "Order #{$model->id}\n";
                $priority = $this->getPriority($model, isset($model->priority_level) ? $model->priority_level - 1 : User::PRIORITY_HIGH);
                $_SESSION['__id'] = $model->created_by;
                if ( $model->priority_level > 0 ) {
                    echo "\tStage: {$model->priority_level}\n";
                    echo "\tDate: {$model->notify_date}\n";
                    if ($this->checkTime($model->notify_date)) {
                        echo "\tPriority: $priority\n";
                        $model->sendAndUpdateTelegramNotifications($model);
                    }
                }
            }
        }
    }

    private function checkTime($timestamp): bool
    {
        $now = time();
        $delay = \Yii::$app->params['notify_delay'];
        return ($now - $timestamp > $delay);
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
        foreach ( $coworkers as $coworker ) {
            echo $coworker->name . "\n";
        }
    }

    public function actionCheck()
    {
        echo Helper::checkService();
    }
}