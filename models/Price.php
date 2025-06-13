<?php
namespace app\models;

class Price extends \yii\db\ActiveRecord 
{
    public function rules()
    {
        return [
            [['price'], 'double', 'required'],
            [['coworker_id'], 'exists', 'targetClass' => Coworker::class, 'targetAttribute' => 'id'],
            [['date'], 'default', 'value' => function () { return \Yii::$app->formatter->saDate(time(), 'php:Y-m-d'); }],
        ];
    }

    public function getCoworker()
    {
        return $this->hasOne(Coworker::class, ['id' => 'coworker_id']);
    }
}