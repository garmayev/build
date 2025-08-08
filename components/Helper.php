<?php

namespace app\components;

use app\models\Order;
use app\models\telegram\TelegramMessage;
use app\models\User;
use yii\base\Component;

class Helper extends Component
{
    const equals = [
        'less' => 'меньше',
        'more' => 'больше',
        'equals' => 'рано',
        'not-equals' => 'не равно',
    ];
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
                $message .= \Yii::t("app", "--- {$attachment->getLink(true)}") . "\n";
            }
        }
        $currentCount = $order->issetCoworkers;
        $totalRequired = $order->requiredCoworkers;
        $message .= "\n" . \Yii::t("app", "<b>Coworkers</b>: <i>{current}/{total}</i>", ["current" => $currentCount, "total" => $totalRequired]) . "\n";
/*        foreach ($order->requirements as $requirement) {
            $eq = Helper::equals[$requirement->type];
            $message .= "--- {$requirement->property->title} {$eq} {$requirement->value} {$requirement->dimension->title}\n";
        } */
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
        $message .= "\n" . \Yii::t("app", "<b>Coworkers</b>: <i>{current}/{total}</i>", ["current" => $currentCount, "total" => $totalRequired]) . "\n";
        return $message;
    }

    public static function orderDetails(Order $order)
    {
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
        $currentCount = $order->issetCoworkers;
        $totalRequired = $order->requiredCoworkers;
        $text .= "\n" . \Yii::t("app", "<b>Coworkers</b>: <i>{current}/{total}</i>", ["current" => $currentCount, "total" => $totalRequired]) . "\n";
        return $text;
    }

    public static function orderDetailsPlain(Order $order)
    {
        $building = $order->building;
        $text = \Yii::t('app', 'Order #{id}', ['id' => $order->id]) . "\n";
        $text .= \Yii::t("app", "Building: {building}", ['building' => $building->title]) . "\n";
        $text .= \Yii::t("app", "Address: {address}", ['address' => $building->location->address]) . "\n";
        $text .= \Yii::t("app", "Date: {date}", ['date' => \Yii::$app->formatter->asDate($order->date)]) . "\n";
        return $text;
    }

    public static function notify(int $user_id, int $order_id, string $text = null, array $keyboard = null)
    {
        $user = User::findOne($user_id);
        $order = Order::findOne($order_id);
        $message = TelegramMessage::findOne(['and', ['chat_id' => $user->profile->chat_id], ['order_id' => $order->id]]);
        if (empty($message)) {
            $message = new TelegramMessage([
                'chat_id' => $user->profile->chat_id,
                'order_id' => $order->id,
            ]);
        }
        if ($text) {
            $message->text = $text;
        } else {
            $message->text = self::orderDetails($order);
        }
        $message->reply_markup = json_encode($keyboard);
        $message->send();
    }

    public static function isPointInCircle($point1, $point2, $radius) 
    {
        // Земной радиус в километрах
        $earthRadius = 6371;
        $radiusKm = $radius / 1000;

        // Переводим градусы в радианы
        $latFrom = deg2rad($point1['latitude']);
        $lonFrom = deg2rad($point1['longitude']);
        $latTo = deg2rad($point2['latitude']);
        $lonTo = deg2rad($point2['longitude']);

        // Разница между координатами
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        // Формула гаверсинусов для расчета расстояния
        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        // Расстояние в километрах
        $distance = $angle * $earthRadius;

        // Проверяем, находится ли точка в пределах радиуса
        return $distance <= $radiusKm;
    }

    public static function timeToSeconds($timeString) {
        $parts = explode(':', $timeString);
        $seconds = 0;

        // Handle HH:MM:SS or MM:SS
        if (count($parts) == 3) { // HH:MM:SS
            $seconds += (int)$parts[0] * 3600; // Hours to seconds
            $seconds += (int)$parts[1] * 60;    // Minutes to seconds
            $seconds += (int)$parts[2];         // Seconds
        } elseif (count($parts) == 2) { // MM:SS
            $seconds += (int)$parts[0] * 60;    // Minutes to seconds
            $seconds += (int)$parts[1];         // Seconds
        } else {
            // Handle invalid format or throw an error
            return false;
        }
        return $seconds;
    }
}