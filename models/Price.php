<?php

namespace app\models;

/**
 * @var int $price
 * @var int $date
 * @var int $user_id
 *
 * @var User $user
 */
class Price extends \yii\db\ActiveRecord
{
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    'createdAtAttribute' => 'date',
                    'updatedAtAttribute' => false,
                ]
            ]
        ];
    }

    public function rules(): array
    {
        return [
            [['price'], 'required'],
            [['price'], 'integer'],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['user'], 'safe'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery+
     */
    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}