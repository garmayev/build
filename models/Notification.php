<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $order_id
 * @property int $coworker_id
 * @property int $type
 * @property int $status
 * @property int $created_at
 *
 * @property Order $order
 * @property Coworker $coworker
 */
class Notification extends ActiveRecord
{
    const TYPE_TELEGRAM = 1;
    const TYPE_PUSH_NOTIFICATION = 2;

    const STATUS_WAITING = 1;
    const STATUS_AGREE = 2;
    const STATUS_DISAGREE = 3;
    const STATUS_DELETED = 4;

    public function behaviors(): array
    {
        return [
            [
                'class' => 'yii\behaviors\TimestampBehavior',
                'createdAtAttribute' => 'created_at',
            ]
        ];
    }

    public function rules(): array
    {
        return [
            [['order_id', 'coworker_id', 'type'], 'required'],
            [['order_id', 'coworker_id', 'type', 'status', 'created_at'], 'integer'],
            [['type'], 'in', 'range' => [self::TYPE_TELEGRAM, self::TYPE_PUSH_NOTIFICATION]],
            [['type'], 'default', 'value' => self::TYPE_TELEGRAM],
            [['status'], 'in', 'range' => [self::STATUS_WAITING, self::STATUS_AGREE, self::STATUS_DISAGREE, self::STATUS_DELETED]],
            [['status'], 'default', 'value' => self::STATUS_WAITING],
            [['created_at'], 'default', 'value' => time()],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getOrder(): ActiveQuery
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCoworker(): ActiveQuery
    {
        return $this->hasOne(User::className(), ['id' => 'coworker_id']);
    }

    public static function send($order_id)
    {
        $order = Order::findOne($order_id);
        $coworkers = $order->suitableCoworkers;
        foreach ($coworkers as $coworker) {
            $notify = self::findOne(['coworker_id' => $coworker->id, 'order_id' => $order_id]);
            $notify->updateNotify();
        }
    }

    public function updateNotify()
    {

    }
}