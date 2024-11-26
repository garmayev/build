<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "material_property".
 *
 * @property int $material_id
 * @property int $property_id
 *
 * @property Material $material
 * @property Property $property
 */
class MaterialProperty extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'material_property';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['material_id', 'property_id'], 'required'],
            [['material_id', 'property_id'], 'integer'],
            [['material_id', 'property_id'], 'unique', 'targetAttribute' => ['material_id', 'property_id']],
            [['material_id'], 'exist', 'skipOnError' => true, 'targetClass' => Material::class, 'targetAttribute' => ['material_id' => 'id']],
            [['property_id'], 'exist', 'skipOnError' => true, 'targetClass' => Property::class, 'targetAttribute' => ['property_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'material_id' => Yii::t('app', 'Material ID'),
            'property_id' => Yii::t('app', 'Property ID'),
        ];
    }

    /**
     * Gets query for [[Material]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMaterial()
    {
        return $this->hasOne(Material::class, ['id' => 'material_id']);
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
