<?php

namespace app\commands;

use app\models\Coworker;
use app\models\Order;
use yii\console\Controller;

class OrderController extends Controller
{
    public function actionStack($order_id)
    {
        $order = Order::findOne($order_id);
        foreach ($order->filters as $filter) {
            echo "{$order->countCoworkersByFilter($filter)}";
        }
    }

    public function actionNotify()
    {
        $orders = Order::find()->where(['status' => Order::STATUS_NEW])->all();
        foreach ($orders as $order) {
            if ($order->notify_date < $order->notify_date + \Yii::$app->params['notify_delay'] && $order->notify_stage > -1) {
                $priority = $order->notify_stage - 1;
                $notification = $order->notify($priority);
                if (count($notification) > 0) {
                    echo "Notifying order #{$order->id}\n";
                    foreach ($notification as $item) {
                        echo "Notify: {$item['firstname']} {$item['lastname']}\n";
                    }
                } else {
                    echo "Notifying order #{$order->id} failed\n";
                }
            }
        }
    }
}