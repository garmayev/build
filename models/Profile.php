<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property string $first_name
 * @property string $last_name
 * @property string $patronymic
 * @property string $biography
 * @property integer $birthday
 * @property-read string $name
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
            [['first_name', 'last_name', 'patronymic', 'biography'], 'string'],
            [['birthday'], 'integer'],

        ];
    }

    public function getName()
    {
        $result = [];
        if (!is_null($this->first_name)) {
            $result[] = $this->first_name;
        }
        if (!is_null($this->last_name)) {
            $result[] = $this->last_name;
        }
        return implode(' ', $result);
    }
}