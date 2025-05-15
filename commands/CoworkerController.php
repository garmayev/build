<?php
namespace app\commands;

class CoworkerController extends \yii\console\Controller
{
    public function actionOrders($coworker_id)
    {
        $coworker = \app\models\Coworker::findOne($coworker_id);

        if (!$coworker) {
            echo \Yii::t("app", "Coworker not found");
        }

        $orders = \app\models\Order::find()
            ->where(['status' => \app\models\Order::STATUS_NEW])
            ->andWhere(['>=', 'priority_level', $coworker->priority])
            ->orderBy(['id' => SORT_DESC])
            ->all();
        $suitableOrders = [];

        foreach ($orders as $order) {
            foreach ($order->filters as $filter) {
                $list = \app\models\Coworker::searchByFilter($filter, $order->priority_level);
                foreach ($list as $item) {
                    if ($coworker->id === $item->id) {
                        $suitableOrders[] = $order;
                    }
                }
            }
        }
        echo "Count: ".count($suitableOrders);
    }
}