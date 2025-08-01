<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "location".
 *
 * @property int $id
 * @property string|null $address
 * @property float|null $latitude
 * @property float|null $longitude
 *
 * @property Building[] $buildings
 * @property string $link
 */
class Location extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            'blameable' => [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => false,
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'location';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['latitude', 'longitude'], 'number'],
            [['address'], 'string', 'max' => 255],
        ];
    }

    public function fields()
    {
        return [
            'id',
            'address',
            'latitude',
            'longitude',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'address' => Yii::t('app', 'Address'),
            'latitude' => Yii::t('app', 'Latitude'),
            'longitude' => Yii::t('app', 'Longitude'),
        ];
    }

    /**
     * Gets query for [[Buildings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBuildings()
    {
        return $this->hasMany(Building::class, ['location_id' => 'id']);
    }

    public function getLink()
    {
        return "<a href='https://2gis.ru/geo/{$this->longitude}%2C{$this->latitude}?m={$this->longitude}%2C{$this->latitude}%2F14'>{$this->address}</a>";
    }
}
