<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "technique_property".
 *
 * @property int $technique_id
 * @property int $property_id
 *
 * @property Property $property
 * @property Technique $technique
 */
class TechniqueProperty extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'technique_property';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['technique_id', 'property_id'], 'required'],
            [['technique_id', 'property_id'], 'integer'],
            [['technique_id', 'property_id'], 'unique', 'targetAttribute' => ['technique_id', 'property_id']],
            [['property_id'], 'exist', 'skipOnError' => true, 'targetClass' => Property::class, 'targetAttribute' => ['property_id' => 'id']],
            [['technique_id'], 'exist', 'skipOnError' => true, 'targetClass' => Technique::class, 'targetAttribute' => ['technique_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'technique_id' => Yii::t('app', 'Technique ID'),
            'property_id' => Yii::t('app', 'Property ID'),
        ];
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

    /**
     * Gets query for [[Technique]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTechnique()
    {
        return $this->hasOne(Technique::class, ['id' => 'technique_id']);
    }
}
