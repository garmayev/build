<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "order_coworker".
 *
 * @property int $order_id
 * @property int $coworker_id
 *
 * @property Coworker $coworker
 * @property Order $order
 */
class OrderCoworker extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_coworker';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'coworker_id'], 'required'],
            [['order_id', 'coworker_id'], 'integer'],
            [['order_id', 'coworker_id'], 'unique', 'targetAttribute' => ['order_id', 'coworker_id']],
            [['coworker_id'], 'exist', 'skipOnError' => true, 'targetClass' => Coworker::class, 'targetAttribute' => ['coworker_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'order_id' => Yii::t('app', 'Order ID'),
            'coworker_id' => Yii::t('app', 'Coworker ID'),
        ];
    }

    /**
     * Gets query for [[Coworker]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCoworker()
    {
        return $this->hasOne(Coworker::class, ['id' => 'coworker_id']);
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
}
