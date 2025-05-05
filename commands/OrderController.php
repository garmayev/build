<?php

namespace app\commands;

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
        foreach ($order->filters as $filter) {
            echo "{$order->countCoworkersByFilter($filter)}";
        }
    }

    public function actionNotify($order_id = null, $priority = Coworker::PRIORITY_HIGH)
    {
        session_start();
        if ($order_id) {
            $model = Order::findOne($order_id);
            $_SESSION['__id'] = $model->created_by;
//            echo $model->notify_stage . "\n";
            if ($model->notify_stage >= 0) {
                if ($this->checkTime($model->notify_date)) {
                    $model->notify($model->notify_stage - 1);
                }
            }
        } else {
            $models = Order::find()->where(['status' => Order::STATUS_NEW])->all();
            foreach ($models as $model) {
                echo "Order #{$model->id}\n";
                $priority = $this->getPriority($model, isset($model->notify_stage) ? $model->notify_stage - 1 : Coworker::PRIORITY_HIGH);
                $_SESSION['__id'] = $model->created_by;
                if ( $model->notify_stage > 0 ) {
                    echo "\tStage: {$model->notify_stage}\n";
                    echo "\tDate: {$model->notify_date}\n";
                    if ($this->checkTime($model->notify_date)) {
                        echo "\tPriority: $priority\n";
                        $model->notify($priority);
                    }
                }
            }
        }
    }

    public function actionCoworkers($order_id, $priority = Coworker::PRIORITY_HIGH)
    {
        session_start();
        $_SESSION['__id'] = 1;
        $model = Order::findOne($order_id);
//        if (isset($model->coworkers)) {
//            echo "$model->id\n";
//        }
        $coworkerList = [];
        foreach ($model->filters as $filter) {
            $coworkerList = array_merge( \app\models\Coworker::searchByFilter($filter, 0), $coworkerList );

            foreach ($coworkerList as $coworker) {
                echo $coworker->firstname." ".$coworker->lastname;
            }
        }
        echo "$order_id\n";
//        echo "Priority: {$this->findCoworkerByPriority($order_id, $priority)}\n";
    }

    private function checkTime($timestamp)
    {
        $now = time();
        $delay = \Yii::$app->params['notify_delay'];
        return ($now - $timestamp > $delay);
    }

    private function getPriority(Order $model, $priority)
    {
        for ($i = $priority; $i >= -1; $i--) {
//            echo "#$i ";
            if (count($model->issetCoworkers($i)) > 0) {
                return $i;
            }
        }
        return false;
    }
}