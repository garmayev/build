<?php

namespace app\models\telegram;

use app\models\Coworker;
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
 * @property integer $status
 * @property integer $message_id
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
            [['device_id', 'text', 'reply_markup'], 'string'],
            [['created_at', 'updated_at', 'status', 'message_id', 'chat_id'], 'integer'],
            [['order_id'], 'exist', 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['status'], 'default', 'value' => self::STATUS_NEW],
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function getSender()
    {
        return $this->hasOne(Coworker::class , ['chat_id' => 'chat_id']);
    }

    public function editMessageText($text, $keyboard = "")
    {
//        \Yii::error($text);
//        \Yii::error($this->text);
        if ($text !== $this->text) {
            $response = \Yii::$app->telegram->editMessageText([
                "chat_id" => $this->chat_id,
                "text" => $text,
                "reply_markup" => !empty($keyboard) ? $keyboard : null,
                "parse_mode" => "html",
                "message_id" => $this->message_id,
            ]);
            if ($response->ok) {
                $this->text = $text;
                $this->reply_markup = json_encode($keyboard);
                $this->save();
            }
        }
    }

    public function deleteMessage()
    {
        \Yii::$app->telegram->deleteMessage(["message_id" => $this->id, "chat_id" => $this->chat_id]);
        $this->delete();
    }

    public static function sendMessage($params)
    {
        $response = \Yii::$app->telegram->sendMessage($params);
        if ($response) {
            $message = new TelegramMessage();
            $message->id = $response["result"]["message_id"];
            $message->message_id = $response["result"]["message_id"];
            $message->chat_id = $params["chat_id"];
            $message->save();
        }
    }

    public function send($text = null, $test = false)
    {
        $curl = curl_init();
        $bot_id = \Yii::$app->params['bot_id'];

        $data = [
            "chat_id" => $this->chat_id,
            "text" => $text ?? $this->text,
            "parse_mode" => "html",
            "reply_markup" => $this->reply_markup,
        ];

        curl_setopt($curl, CURLOPT_URL, "https://api.telegram.org/bot{$bot_id}/sendMessage");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER, false);

        if (($result = curl_exec($curl)) === false) {
            \Yii::error(curl_error($curl));
        }
        $raw = json_decode($result, true);
        if ($raw["ok"]) {
            $this->id = $raw["result"]["message_id"];
            $this->message_id = $raw["result"]["message_id"];
            if ( !$this->save() ) {
                \Yii::error($this->errors);
            }
            curl_close($curl);
            return $result;
        } else {
            \Yii::error($raw);
            curl_close($curl);
            return "Something wrong";
        }
    }

    public function editText($chat_id = null, $text = null, $reply_markup = null, $message_id = null)
    {
        $this->chat_id = $chat_id ?? $this->chat_id;
        $this->text = $text ?? $this->text;
        $this->reply_markup = $reply_markup ?? $this->reply_markup;
        $this->message_id = $message_id ?? $this->message_id;

        $curl = curl_init();
        $bot_id = \Yii::$app->params['bot_id'];
        $data = [
            "chat_id" => $this->chat_id,
            "text" => $this->text,
            "parse_mode" => "html",
            "reply_markup" => $this->reply_markup,
            "message_id" => $this->message_id,
        ];

        curl_setopt($curl, CURLOPT_URL, "https://api.telegram.org/bot{$bot_id}/editMessageText");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER, false);

        if (($result = curl_exec($curl)) === false) {
            \Yii::error(curl_error($curl));
        }

        curl_setopt($curl, CURLOPT_URL, "https://api.telegram.org/bot{$bot_id}/editMessageReplyMarkup");

        if ($result = curl_exec($curl) === false) {
            \Yii::error(curl_error($curl));
        }

        $this->save();
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

        if (($result = curl_exec($curl)) === false) {
            \Yii::error(curl_error($curl));
            return curl_error($curl);
        } else {
            curl_close($curl);
            $this->delete();
            return $result;
        }
    }
}
