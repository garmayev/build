<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property string $name
 * @property string $value
 */
class Config extends ActiveRecord
{
    public function rules()
    {
        return [
            [['name', 'value'], 'required'],
            [['name', 'value', 'label'], 'string'],
        ];
    }

    public function attributeLabels() 
    {
        return [
            'name' => \Yii::t('app', 'Name'),
            'label' => \Yii::t('app', 'Title'),
            'value' => \Yii::t('app', 'Value'),
        ];
    }
}