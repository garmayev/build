<?php
namespace app\modules\messenger\models;

class TelegramMessage extends \yii\base\Model
{
    public $from;
    public $chat;
    public $date;
    public $text;
    public $entities;
    public $edit_date;
    public $message_id;
    public $reply_markup;

    public function rules() 
    {
        return [
            [['date', 'edit_date'], 'integer'],
            [['from'], 'targetClass' => TelegramUser::class],
            [['chat'], 'targetClass' => TelegramChat::class],
        ];
    }

    public function setFrom($data)
    {
        $this->from = new TelegramFrom($data);
    }

    public function setChat($data)
    {
        $this->chat = new TelegramChat($data);
    }

    public function setReply_markup($data)
    {
        $this->reply_markup = json_decode($data, true);
    }
}