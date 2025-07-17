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
 * @property User $user
 * @property Order $order
 * @property float $price
 * @property int $debit
 * @property int $credit
 */
class Hours extends ActiveRecord 
{
    public static function tableName(): string
    {
        return "hours";
    }

    public function rules(): array
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'order_id'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['count'], 'integer'],
            [['is_payed'], 'boolean'],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'coworker_id' => \Yii::t('app', 'Coworker ID'),
            'count' => \Yii::t('app', 'Count'),
            'date' => \Yii::t('app', 'Date'),
            'is_payed' => \Yii::t('app', 'Is payed')
        ];
    }

    public function fields(): array
    {
        return [
            'user_id',
            'date',
            'count',
            'is_payed',
            'order_id',
            'price' => function (Hours $model) {
                return $model->getPrice();
            },
            'debit',
            'credit'
        ];
    }

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'coworker_id']);
    }

    public function getOrder(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function getPrice(): float
    {
        $priceModel = Price::find()
            ->where(['user_id' => $this->user_id])
            ->andWhere(['>=', 'date', $this->date])
            ->orderBy(['date' => SORT_DESC])
            ->one();
//        \Yii::error($priceModel->createCommand()->getRawSql());
//        $priceModel = $priceModel->one();
        return $priceModel ? $priceModel->price : 0;
    }

    public function getDebit(): float
    {
        if ($this->is_payed) {
            return $this->count * $this->price;
        }
        return 0;
    }

    public function getCredit(): float
    {
        if (!$this->is_payed) {
            return $this->count * $this->price;
        }
        return 0;
    }
}