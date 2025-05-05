<?php

namespace app\models;

use yii\base\Model;

class Telegram extends Model
{
    private $curl;
    private $bit_id;

    public function init()
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_HEADER, false);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    }

    public static function setWebhook()
    {
        $bot_id = \Yii::$app->params['bot_id'];
        $args = http_build_query([
            "url" => "https://build.amgcompany.ru/api/telegram/callback"
        ]);

        return Telegram::send("https://api.telegram.org/bot{$bot_id}/setwebhook?$args");
    }

    public static function sendMessage($chat_id, $message, $keyboard)
    {
        $bot_id = \Yii::$app->params['bot_id'];
        $data = [
            "chat_id" => urlencode($chat_id),
            "text" => urlencode($message),
            "reply_markup" => json_encode([
                "inline_keyboard" => $keyboard
            ])
        ];

        return Telegram::send("https://api.telegram.org/bot{$bot_id}/sendMessage", $data);
    }

    public static function editMessage($chat_id, $message_id, $message, $keyboard)
    {
        $bot_id = \Yii::$app->params['bot_id'];
        $data = [
            "chat_id" => $chat_id,
            "text" => $message,
            "message_id" => $message_id,
        ];

        if ( isset($keyboard) && count($keyboard) ) {
            $data["reply_markup"] = json_encode([
                "inline_markup" => $keyboard
            ]);
        }

        return Telegram::send("https://api.telegram.org/bot{$bot_id}/editMessageText", $data);
    }

    public static function deleteMessage()
    {
    }

    public static function send($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ( ($result = curl_exec($curl)) === false ) {
            \Yii::error(curl_error($curl));
            return null;
        }

        curl_close($curl);
        return $result;
    }
}