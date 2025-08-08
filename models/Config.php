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
            [['name', 'value'], 'string'],
        ];
    }
}