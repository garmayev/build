<?php

namespace app\components;

use app\models\Order;
use yii\base\Component;

class Helper extends Component
{
    public static function generateTelegramMessage($order_id)
    {
        $order = Order::findOne($order_id);

        $building = $order->building;
//        $message = \Yii::t("app", "Order #{id}", ['id' => $order->id]) . "\n";
        $message = \Yii::t("app", "<b>Building</b>: <i>{building}</i>", ['building' => $building->title]) . "\n";
        $message .= \Yii::t("app", "<b>Address</b>: <i>{address}</i>", ['address' => $building->location->link]) . "\n";
        $message .= \Yii::t("app", "<b>Date</b>: <i>{date}</i>", ['date' => \Yii::$app->formatter->asDate($order->created_at)]) . "\n";
        if ($order->comment) {
            $message .= \Yii::t("app", "<b>Comment</b>: {comment}", ['comment' => $order->comment]) . "\n";
        }
        if ($order->attachments) {
            $message .= \Yii::t("app", "<b>Attachments</b>")."\n";
            foreach ($order->attachments as $attachment) {
                $message .= \Yii::t("app", "--- {$attachment->getLink(true)}");
            }
        }
        $currentCount = $order->issetCoworkers;
        $totalRequired = $order->requiredCoworkers;
        $message .= "\n" . \Yii::t("app", "<b>Requirements</b>: <i>{current}/{total}</i>", ["current" => $currentCount, "total" => $totalRequired]) . "\n";
//        $message .= \Yii::t("app", "Requirements:") . "\n";
        foreach ($order->filters as $filter) {
            $message .= "- {$filter->category->title}\n";
            foreach ($filter->requirements as $requirement) {
                $message .= "--- {$requirement->property->title} {$requirement->type} {$requirement->value} {$requirement->dimension->title}\n";
            }
        }
        return $message;
    }

    public static function generateTelegramHiddenMessage($order_id) 
    {
        $order = Order::findOne($order_id);

        $building = $order->building;
        $message = \Yii::t("app", "Order #{id}", ['id' => $order->id]) . "\n";
//        $message .= \Yii::t("app", "Building: {building}", ['building' => $building->title]) . "\n";
//        $message .= \Yii::t("app", "Address: {address}", ['address' => $building->location->link]) . "\n";
        $message .= \Yii::t("app", "Date: {date}", ['date' => \Yii::$app->formatter->asDate($order->created_at)]) . "\n";
        return $message;
    }
}