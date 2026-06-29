<?php

namespace app\models\telegram;

use app\models\Coworker;
use app\models\Order;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use garmayev\max\MessageBuilder;

/**
 * @property string|null $chat_id
 * @property string|null $device_id
 * @property string $text
 * @property string $reply_markup
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $order_id
 * @property integer $status
 * @property integer $message_id
 * @property string $joined
 *
 * @property Coworker $sender
 */
class TelegramMessage extends ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_AGREE = 1;
    const STATUS_DECLINE = 2;

    public static function tableName(): string
    {
        return "{{%telegram_message}}";
    }

    public function rules()
    {
        return [
            [['device_id', 'text', 'joined', 'message_id'], 'string'],
            [['created_at', 'updated_at', 'status', 'chat_id'], 'integer'],
            [['order_id'], 'exist', 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['status'], 'default', 'value' => self::STATUS_NEW],
            [['reply_markup'], 'safe']
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function send()
    {
        if (empty($this->chat_id)) return;

        $max = \Yii::$app->max;

        $order = \app\models\Order::findOne($this->order_id);
        $text = \app\components\Helper::orderDetails($order, 'max');

        $message = MessageBuilder::create($text)
            ->inlineKeyboard((array)$this->reply_markup)
            ->format('html');

        foreach ($order->attachments as $attachment) {
            if ($attachment->isImage()) {
                $message->image(\yii\helpers\Url::to($attachment->url, true));
            }
        }
        $messageBuilded = $message->build();
        $response = $max->sendMessage($messageBuilded, ['user_id' => $this->chat_id]);

        $this->message_id = $response->message['body']['mid'];
        if (is_array($messageBuilded['attachments'])) {
            $this->reply_markup = json_encode($messageBuilded['attachments']);
        }
        if (!$this->save()) {
            \Yii::error($this->errors);
        }
    }
}
