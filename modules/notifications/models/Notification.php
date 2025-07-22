<?php

namespace app\modules\notifications\models;

use yii\db\ActiveRecord;

/**
 * @property string $title
 * @property string $message
 * @property string $recipient
 * @property string $channels
 * @property string|array $actions
 */
class Notification extends ActiveRecord
{
    const CHANNEL_TELEGRAM = 'telegram';
    const CHANNEL_FCM = 'fcm';
    const CHANNEL_APN = 'apn';

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => false,
                ]
            ]
        ];
    }

    public static function tableName()
    {
        return '{{%notifications}}';
    }

    public function rules()
    {
        return [
            [['title', 'message', 'recipient', 'channels'], 'required'],
            [['channels'], 'in',
                'range' => [self::CHANNEL_TELEGRAM, self::CHANNEL_FCM, self::CHANNEL_APN],
                'allowArray' => true],
            [['actions'], 'safe']
        ];
    }

    public function beforeSave($insert)
    {
        if (is_array($this->channels)) {
            $this->channels = implode($this->channels, ',');
        }
        if (is_array($this->actions)) {
            $this->actions = json_encode($this->actions);
        }
        return parent::beforeSave($insert);
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->channels = explode(',', $this->channels);
        if (!empty($this->actions)) {
            $this->actions = json_decode($this->actions, true);
        } else {
            $this->actions = [];
        }
    }

    public function getChannels()
    {
        return $this->channels;
    }

    public function getActions()
    {
        return $this->actions;
    }
}