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

    public function actionNotify($order_id = null, $priority = Coworker::PRIORITY_HIGH)
    {
        session_start();
        $models = Order::find()->where(['status' => Order::STATUS_NEW])->all();
        echo "[" . \Yii::$app->formatter->asDatetime(time(), "php:Y-m-d H:i:s") . "]\n";
        echo "Count: " . count($models) . "\n";
        try {
            foreach ($models as $model) {
                $priority = $this->getPriority($model, isset($model->priority_level) ? $model->priority_level - 1 : Coworker::PRIORITY_HIGH);
                $_SESSION['__id'] = $model->created_by;
                if ($model->priority_level >= 0 && $this->checkOrderMode($model)) {
                    echo "Order #{$model->id}\n";
                    if ($this->checkTime($model->notify_date, $model)) {
                        echo "\tOrder {$model->id} is needle to notify\n";
                        echo "\tPriority: $priority\n";
                        $model->priority_level = $model->priority_level--;
                        $model->notify_date = time();
                        if ($model->save()) {
                            foreach ($model->suitableCoworkers as $key => $coworker) {
                                echo $key + 1 . " {$coworker->name}\n";
                            }
                            $model->sendAndUpdateTelegramNotifications();
                        } else {
                            \Yii::error($model->errors);
                        }
                    }
                } else {
                    echo "Order #{$model->id} is not needle to notify\n";
                }
            }
        } catch (\Exception $e) {
            
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

    private function checkOrderMode(Order $model)
    {
        $now = \Yii::$app->formatter->asDate(time(), "php:Y-m-d 00:00:00");
        switch ($model->mode) {
            case Order::MODE_SINGLE_FIXED:
                return $model->start_datetime === $now;
            case Order::MODE_LONG_FIXED:
            case Order::MODE_LONG_DAILY:
                return $model->finish_datetime >= $now;
        }
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
        echo "Suitable coworkers for order #{$id}\n";
        foreach ($order->requirements as $requirement)
        {
            echo "\tRequirement:\n\t\tcount:{$requirement->count}\n\t\t{$requirement->property->title} {$requirement->type} {$requirement->value}\n";
        }
        if (count($coworkers) === 0) {
            echo "Empty\n";
        }
        /**
         * @var $coworker Coworker
         */
        foreach ($coworkers as $coworker) {
            echo $coworker->id . " " . $coworker->name .":\n";
            foreach ($coworker->userProperties as $property) {
                echo "\t{$property->property->title}: {$property->value}\n";
            }
        }
        echo "Total: " . count($coworkers) . "\n\n";
    }

    public function actionIsOwnerNotified($order_id)
    {
        $model = \app\models\Order::findOne($order_id);
        echo "Model {$order_id}\n";
        echo "{$model->owner->profile->chat_id}\n";
        $message = \app\models\telegram\TelegramMessage::find()->where(['chat_id' => $model->owner->profile->chat_id])->andWhere(['order_id' => $order_id])->one();
        echo $message->text."\n";
        if ($model->isOwnerNotified()) {
            echo "Order #{$order_id} already Notified\n";
        } else {
            echo "Not notified\n";
        }
    }
}