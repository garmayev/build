<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "building".
 *
 * @property int $id
 * @property string|null $title
 * @property int|null $location_id
 *
 * @property Location $location
 * @property Order[] $orders
 */
class Building extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'blameable' => [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => false,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_VALIDATE => ['user_id']
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'building';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['location_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['location_id'], 'exist', 'skipOnError' => true, 'targetClass' => Location::class, 'targetAttribute' => ['location_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['location'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'location_id' => Yii::t('app', 'Location ID'),
        ];
    }

    public function fields()
    {
        return [
            'title',
            'location' => function (Building $model) {
                return $model->location;
            }
        ];
    }

    /**
     * Gets query for [[Location]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLocation()
    {
        return $this->hasOne(Location::class, ['id' => 'location_id']);
    }

    public function setLocation($data)
    {
        $location = Location::find()
            ->where(['address' => $data['address']])
            ->andWhere(['latitude' => $data['latitude']])
            ->andWhere(['longitude' => $data['longitude']])
            ->one();
        $this->save(false);
        if ($location) {
            $this->link('location', $location);
        } else {
            $location = new Location($data);
            if ($location->save()) {
                $this->link('location', $location);
            } else {
                \Yii::error($location->getErrorSummary(true));
            }
        }
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['building_id' => 'id']);
    }
}
