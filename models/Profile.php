<?php

namespace app\models;

use floor12\phone\PhoneValidator;
use yii\db\ActiveRecord;

/**
 * @property string $family
 * @property string $surname
 * @property string $name
 * @property string $birthday
 * @property string $phone
 * @property string $chat_id
 * @property string $device_id
 *
 * @property string $fullName
 */
class Profile extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%profile}}';
    }

    public function rules()
    {
        return [
            [['family', 'name', 'surname', 'birthday', 'phone', 'chat_id', 'device_id'], 'string'],
            [['phone'], PhoneValidator::class],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('app', 'ID'),
            'family' => \Yii::t('app', 'Family'),
            'name' => \Yii::t('app', 'Name'),
            'surname' => \Yii::t('app', 'Surname'),
            'birthday' => \Yii::t('app', 'Birthday'),
            'phone' => \Yii::t('app', 'Phone'),
            'chat_id' => \Yii::t('app', 'Telegram Chat ID'),
            'device_id' => \Yii::t('app', 'Device ID'),
        ];
    }

    public function getFullName(): string
    {
        return trim("$this->family $this->name $this->surname");
    }
}