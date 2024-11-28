<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dimension".
 *
 * @property int $id
 * @property string $title
 * @property float $multiplier
 * @property string|null $short
 *
 * @property Filter[] $filters
 * @property Property[] $properties
 * @property PropertyDimension[] $propertyDimensions
 */
class Dimension extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dimension';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'multiplier'], 'required'],
            [['multiplier'], 'number'],
            [['title', 'short'], 'string', 'max' => 255],
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
            'multiplier' => Yii::t('app', 'Multiplier'),
            'short' => Yii::t('app', 'Short'),
        ];
    }

    /**
     * Gets query for [[Filters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFilters()
    {
        return $this->hasMany(Filter::class, ['dimension_id' => 'id']);
    }

    /**
     * Gets query for [[Properties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProperties()
    {
        return $this->hasMany(Property::class, ['id' => 'property_id'])->viaTable('property_dimension', ['dimension_id' => 'id']);
    }

    /**
     * Gets query for [[PropertyDimensions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPropertyDimensions()
    {
        return $this->hasMany(PropertyDimension::class, ['dimension_id' => 'id']);
    }
}
