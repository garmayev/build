<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "order_filter".
 *
 * @property int $order_id
 * @property int $filter_id
 *
 * @property Filter $filter
 * @property Order $order
 */
class OrderFilter extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_filter';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'filter_id'], 'required'],
            [['order_id', 'filter_id'], 'integer'],
            [['order_id', 'filter_id'], 'unique', 'targetAttribute' => ['order_id', 'filter_id']],
            [['filter_id'], 'exist', 'skipOnError' => true, 'targetClass' => Filter::class, 'targetAttribute' => ['filter_id' => 'id']],
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
            'filter_id' => Yii::t('app', 'Filter ID'),
        ];
    }

    /**
     * Gets query for [[Filter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFilter()
    {
        return $this->hasOne(Filter::class, ['id' => 'filter_id']);
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
