<?php
namespace app\commands;

class CoworkerController extends \yii\console\Controller
{
    public function actionMyOrders($chat_id)
    {
        $user = \app\models\User::find()
            ->joinWith("profile")
            ->where(["profile.chat_id" => $chat_id])
            ->one();

        if (!$user) {
            echo \Yii::t("app", "User not found");
        }

        $orders = $user->orders;
        foreach ($orders as $order) {
            echo \Yii::t("app", "Order #{id}", ['id' => $order->id])."\n";
        }

        echo "Count: ".count($orders)."\n";
    }

    public function actionSuitableOrders($coworker_id)
    {
        $coworker = \app\models\User::findOne($coworker_id);

        if (!$coworker) {
            echo \Yii::t("app", "Coworker not found");
        }

        $suitableOrders = $coworker->getSuitableOrders()->all();
        foreach ($suitableOrders as $order) {
            echo \Yii::t("app", "Order #{id}", ['id' => $order->id])."\n";
        }
        echo "Count: ".count($suitableOrders)."\n";
    }
}