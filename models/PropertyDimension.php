<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "property_dimension".
 *
 * @property int $property_id
 * @property int $dimension_id
 *
 * @property Dimension $dimension
 * @property Property $property
 */
class PropertyDimension extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'property_dimension';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['property_id', 'dimension_id'], 'required'],
            [['property_id', 'dimension_id'], 'integer'],
            [['property_id', 'dimension_id'], 'unique', 'targetAttribute' => ['property_id', 'dimension_id']],
            [['dimension_id'], 'exist', 'skipOnError' => true, 'targetClass' => Dimension::class, 'targetAttribute' => ['dimension_id' => 'id']],
            [['property_id'], 'exist', 'skipOnError' => true, 'targetClass' => Property::class, 'targetAttribute' => ['property_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'property_id' => Yii::t('app', 'Property ID'),
            'dimension_id' => Yii::t('app', 'Dimension ID'),
        ];
    }

    /**
     * Gets query for [[Dimension]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDimension()
    {
        return $this->hasOne(Dimension::class, ['id' => 'dimension_id']);
    }

    /**
     * Gets query for [[Property]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProperty()
    {
        return $this->hasOne(Property::class, ['id' => 'property_id']);
    }
}
