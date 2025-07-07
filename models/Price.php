<?php
namespace app\models;

class Price extends \yii\db\ActiveRecord 
{
    public function rules()
    {
        return [
            [['price'], 'required'],
            [['price'], 'number'],
            [['coworker_id'], 'exist', 'targetClass' => Coworker::class, 'targetAttribute' => ['coworker_id' => 'id']],
            [['date'], 'default', 'value' => function () { return \Yii::$app->formatter->saDate(time(), 'php:Y-m-d'); }],
        ];
    }

    public function getCoworker()
    {
        return $this->hasOne(Coworker::class, ['id' => 'coworker_id']);
    }
}