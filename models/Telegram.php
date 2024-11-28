<?php

namespace app\models;

use yii\base\Model;

class Telegram extends Model
{
    public static function setWebhook()
    {
        $curl = curl_init();
        $bot_id = \Yii::$app->params['bot_id'];
        curl_setopt($curl, CURLOPT_URL, "https://api.telegram.org/bot{$bot_id}/setWebhook?" .
            http_build_query([
                'url' => 'https://build.amgcompany.ru/api/telegram/callback'
            ]));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);

        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public static function sendMessage($chat_id, $message, $keyboard)
    {
        $curl = curl_init();
        $bot_id = \Yii::$app->params['bot_id'];
        $data = [
            "chat_id" => urlencode($chat_id),
            "text" => urlencode($message),
            "reply_markup" => json_encode([
                "inline_keyboard" => $keyboard
            ])
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

    public static function editMessage($chat_id, $message_id, $message, $keyboard)
    {
        $curl = curl_init();
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

        curl_setopt($curl, CURLOPT_URL, "https://api.telegram.org/bot{$bot_id}/editMessageText");
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

    public static function deleteMessage()
    {

    }
}