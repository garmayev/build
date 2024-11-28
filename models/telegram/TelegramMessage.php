<?php

namespace app\models\telegram;

use app\models\Order;
use yii\db\ActiveRecord;

/**
 * @property string|null $chat_id
 * @property string|null $device_id
 * @property string $text
 * @property string $reply_markup
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $order_id
 */
class TelegramMessage extends ActiveRecord
{
    public static function tableName(): string
    {
        return "{{%telegram_message}}";
    }

    public function rules()
    {
        return [
            [['chat_id', 'device_id', 'text', 'reply_markup'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['order_id'], 'exist', 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']]
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function send()
    {
        $curl = curl_init();
        $bot_id = \Yii::$app->params['bot_id'];
        $data = [
            "chat_id" => urlencode($this->chat_id),
            "text" => urlencode($this->text),
            "reply_markup" => $this->reply_markup
        ];

        curl_setopt($curl, CURLOPT_URL, "https://api.telegram.org/bot{$bot_id}/sendMessage");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER, false);

        if ( ($result = curl_exec($curl)) === false ) {
            \Yii::error(curl_error($curl));
        }

        curl_close($curl);
        return $result;
    }

    public function edit()
    {
        $curl = curl_init();
        $bot_id = \Yii::$app->params['bot_id'];
        $data = [
            "chat_id" => $this->chat_id,
            "text" => $this->text,
            "message_id" => $this->id,
        ];

        if ( isset($this->reply_markup) ) {
            $data["reply_markup"] = $this->reply_markup;
        }

        curl_setopt($curl, CURLOPT_URL, "https://api.telegram.org/bot{$bot_id}/editMessageText");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER, false);

        if ( ($result = curl_exec($curl)) === false ) {
            \Yii::error(curl_error($curl));
            return curl_error($curl);
        } else {
            curl_close($curl);
            $this->save();
            return $result;
        }
    }

    public function remove()
    {
        $curl = curl_init();
        $bot_id = \Yii::$app->params['bot_id'];
        $data = [
            "chat_id" => $this->chat_id,
            "message_id" => $this->id,
        ];

        curl_setopt($curl, CURLOPT_URL, "https://api.telegram.org/bot{$bot_id}/deleteMessage");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER, false);

        if ( ($result = curl_exec($curl)) === false ) {
            \Yii::error(curl_error($curl));
            return curl_error($curl);
        } else {
            curl_close($curl);
            $this->delete();
            return $result;
        }
    }
}