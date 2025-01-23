<?php
namespace app\modules\messenger\models;

class Telegram extends \yii\base\Model
{
    public $data;
    public $botId;
    public $message;
    public $callback;

    protected $url;

    public function init()
    {
        $this->data = json_decode(file_get_contents("php://input"), true);
        $this->botId = \Yii::$app->params["bot_id"];
        $this->url = "https://api.telegram.org/bot{$this->botId}/";

        if ($this->data && isset($this->data["message"])) {
            $this->message = new Message($this->data["message"]);
            \Yii::$app->session->open($this->data["message"]["from"]["id"]);
        }

        if ($this->data && isset($this->data["callback_query"])) {
            $this->callback = new TelegramCallback($this->data["callback_query"]);
            \Yii::$app->session->open($this->data["callback_query"]["from"]["id"]);
        }
    }

    public function sendMessage($chat_id: string, $text: string, $parse_mode: string = 'html', $reply_markup: string = '')
    {
        $this->send("sendMessage", [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => $parse_mode,
            'reply_markup' => $reply_markup
        ]);
    }

    public function editMessage($chat_id: string, $message_id: integer, $text: string, $parse_mode: string = 'html', $reply_markup: string = '')
    {
        $this->send("editMessageText", [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => $parse_mode,
            'message_id' => $message_id,
        ]);
        $this->send("editMessageReplyMarkup", [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'reply_markup' => $reply_markup
        ]);
    }

    private function send($action: string, $data: array = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "{$this->url}{$action}");
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ( ($result = curl_exec($curl)) === false ) {
            \Yii::error(curl_error($curl));
        }

        curl_close();
        return $result;
    }
}