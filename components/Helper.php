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
        $message = \Yii::t("app", "Order #{id}", ['id' => $order->id]);
        $message .= \Yii::t("app", "Building: {building}", ['building' => $building->title]) . "\n";
        $message .= \Yii::t("app", "Address: {address}", ['address' => $building->location->link]) . "\n";
        $message .= \Yii::t("app", "Date: {date}", ['date' => \Yii::$app->formatter->asDate($order->created_at)]) . "\n";
        if ($order->comment) {
            $message .= \Yii::t("app", "Comment: {comment}", ['comment' => $order->comment]);
        }
        foreach ($order->attachments as $attachment) {
            $message .= \Yii::t("app", "Attachment: {$attachment->getLink(true)}");
        }
        $currentCount = $order->issetCoworkers;
        $totalRequired = $order->requiredCoworkers;
        $message .= "\n" . \Yii::t("app", "Require: {current}/{total}", ["current" => $currentCount, "total" => $totalRequired]);
        $message .= \Yii::t("app", "Requirements:") . "\n";
        foreach ($order->filters as $filter) {
            $message .= "- {$filter->category->title}\n";
            foreach ($filter->requirements as $requirement) {
                $message .= "--- {$requirement->property->title} {$requirement->type} {$requirement->value} {$requirement->dimension->title}\n}";
            }
        }
        return $message;
    }
}