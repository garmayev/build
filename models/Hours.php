<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $coworker_id
 * @property int $order_id
 * @property int $count
 * @property bool $is_payed
 * @property string $date
 *
 * @property Coworker $coworker
 * @property Order $order
 * @property float $price
 */
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
            [['coworker_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['coworker_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['count'], 'integer'],
            [['is_payed'], 'boolean'],
            [['date'], 'default', 'value' => date('Y-m-d')],
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

    public function fields()
    {
        return [
            'coworker_id',
            'date',
            'count',
            'is_payed',
            'order_id',
            'current_price' => function (Hours $model) {
                if ($model->price) {
                    return $model->price;
                }
            },
            'payed_value' => function (Hours $model) {
                if ($model->is_payed) {
                    $price = $model->price ? $model->price : 0;
                    return $model->count * $price;
                }
                return 0;
            },
            'unpayed_value' => function (Hours $model) {
                if (!$model->is_payed) {
                    $price = $model->price ? $model->price : 0;
                    return $model->count * $price;
                }
                return 0;
            }
        ];
    }

    public function getCoworker() {
        return $this->hasOne(Coworker::class, ['id' => 'coworker_id']);
    }

    public function getOrder() {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function getPrice()
    {
        $priceModel =  $this->hasOne(Price::class, ['coworker_id' => 'coworker_id'])->andWhere(['<=', 'date', $this->date])->orderBy(['date' => SORT_DESC]);
        return $priceModel->one() ? $priceModel->one()->price : 0;
    }
}