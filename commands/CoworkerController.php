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

        echo "Count: ".count($coworker->suitableOrders);
    }
}