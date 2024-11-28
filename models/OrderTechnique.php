<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "order_technique".
 *
 * @property int $order_id
 * @property int $technique_id
 *
 * @property Order $order
 * @property Technique $technique
 */
class OrderTechnique extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_technique';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'technique_id'], 'required'],
            [['order_id', 'technique_id'], 'integer'],
            [['order_id', 'technique_id'], 'unique', 'targetAttribute' => ['order_id', 'technique_id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['technique_id'], 'exist', 'skipOnError' => true, 'targetClass' => Technique::class, 'targetAttribute' => ['technique_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'order_id' => Yii::t('app', 'Order ID'),
            'technique_id' => Yii::t('app', 'Technique ID'),
        ];
    }

    /**
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    /**
     * Gets query for [[Technique]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTechnique()
    {
        return $this->hasOne(Technique::class, ['id' => 'technique_id']);
    }
}
