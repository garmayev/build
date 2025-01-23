<?php

namespace app\commands;

use app\models\Coworker;
use app\models\Order;
use yii\console\Controller;

class OrderController extends Controller
{
    public function actionNotify()
    {
        $orders = Order::find()->where(['status' => Order::STATUS_NEW])->andWhere(['notify_status' => Coworker::PRIORITY_HIGH])->all();
        foreach ($orders as $order) {
            var_dump($order->id);
        }
    }
}