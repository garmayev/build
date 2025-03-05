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
            [['order_id', 'coworker_id'], 'required'],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['coworker_id'], 'exist', 'skipOnError' => true, 'targetClass' => Coworker::className(), 'targetAttribute' => ['coworker_id' => 'id']],
            [['count'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'order_id' => \Yii::t('app', 'Order ID'),
            'coworker_id' => \Yii::t('app', 'Coworker ID'),
            'count' => \Yii::t('app', 'Count'),
            'date' => \Yii::t('app', 'Date'),
        ];
    }
}