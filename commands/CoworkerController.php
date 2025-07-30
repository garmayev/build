<?php
namespace app\commands;

class CoworkerController extends \yii\console\Controller
{
    public function actionOrders($coworker_id)
    {
        $coworker = \app\models\User::findOne($coworker_id);

        if (!$coworker) {
            echo \Yii::t("app", "Coworker not found");
        }

        $suitableOrders = $coworker->suitableOrders;
        foreach ($suitableOrders as $order) {
            echo \Yii::t("app", "Order #{id}", ['id' => $order->id])."\n";
        }
        echo "Count: ".count($suitableOrders);
    }
}