<?php

namespace app\models;

use yii\db\ActiveRecord;
class Hours extends ActiveRecord 
{
    public static function tableName() 
    {
        return "hours";
    }

    public function rules()
    {
        return [
            [['coworker_id'], 'required'],
            [['coworker_id', 'order_id'], 'integer'],
            [['coworker_id'], 'exist', 'skipOnError' => true, 'targetClass' => Coworker::className(), 'targetAttribute' => ['coworker_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['count'], 'integer'],
            [['is_payed'], 'boolean']
        ];
    }

    public function attributeLabels()
    {
        return [
            'coworker_id' => \Yii::t('app', 'Coworker ID'),
            'count' => \Yii::t('app', 'Count'),
            'date' => \Yii::t('app', 'Date'),
            'is_payed' => \Yii::t('app', 'Is payed')
        ];
    }

    public function getCoworker() {
        return $this->hasOne(Coworker::class, ['id' => 'coworker_id']);
    }

    public function getOrder() {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }
}