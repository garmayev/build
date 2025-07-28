<?php

namespace app\components;

use app\models\Order;
use app\models\User;
use yii\base\Component;

class Helper extends Component
{
    public static function generateTelegramMessage($order_id)
    {
        $order = Order::findOne($order_id);

        $building = $order->building;
        $message = \Yii::t("app", "<b>Building</b>: <i>{building}</i>", ['building' => $building->title]) . "\n";
        $message .= \Yii::t("app", "<b>Address</b>: <i>{address}</i>", ['address' => $building->location->link]) . "\n";
        $message .= \Yii::t("app", "<b>Date</b>: <i>{date}</i>", ['date' => \Yii::$app->formatter->asDate($order->date)]) . "\n";
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
//        foreach ($order->filters as $filter) {
//            $message .= "- {$filter->category->title}\n";
            foreach ($order->requirements as $requirement) {
                $message .= "--- {$requirement->property->title} {$requirement->type} {$requirement->value} {$requirement->dimension->title}\n";
            }
//        }
        return $message;
    }

    public static function generateTelegramHiddenMessage($order_id) 
    {
        $order = Order::findOne($order_id);

        $building = $order->building;
        $message = "<b>".\Yii::t("app", "Order #{id}", ['id' => $order->id]) . "</b>\n";
        $message .= \Yii::t("app", "<b>Building</b>: <i>{building}</i>", ['building' => $building->title]) . "\n";
        $message .= \Yii::t("app", "<b>Address</b>: <i>{address}</i>", ['address' => $building->location->link]) . "\n";
        $message .= \Yii::t("app", "<b>Date</b>: <i>{date}</i>", ['date' => \Yii::$app->formatter->asDate($order->date)]) . "\n";
        if ($order->comment) {
            $message .= \Yii::t("app", "<b>Comment</b>: {comment}", ['comment' => $order->comment]) . "\n";
        }
        $currentCount = $order->issetCoworkers;
        $totalRequired = $order->requiredCoworkers;
        $message .= "\n" . \Yii::t("app", "<b>Requirements</b>: <i>{current}/{total}</i>", ["current" => $currentCount, "total" => $totalRequired]) . "\n";
        return $message;
    }

    public static function orderDetails(Order $order) {
        $building = $order->building;
        $text = "<b>" . \Yii::t('app', 'Order #{id}', ['id' => $order->id]) . "</b>\n";
        $text .= \Yii::t("app", "<b>Building</b>: <i>{building}</i>", ['building' => $building->title]) . "\n";
        $text .= \Yii::t("app", "<b>Address</b>: <i>{address}</i>", ['address' => $building->location->link]) . "\n";
        $text .= \Yii::t("app", "<b>Date</b>: <i>{date}</i>", ['date' => \Yii::$app->formatter->asDate($order->date)]) . "\n";
        if ($order->comment) {
            $text .= \Yii::t("app", "<b>Comment</b>: {comment}", ['comment' => $order->comment]) . "\n";
        }
        if ($order->attachments) {
            $text .= \Yii::t("app", "<b>Attachments</b>")."\n";
            foreach ($order->attachments as $attachment) {
                $text .= \Yii::t("app", "--- {$attachment->getLink(true)}") . "\n";
            }
        }
        return $text;
    }
}