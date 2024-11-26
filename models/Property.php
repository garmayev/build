<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "property".
 *
 * @property int $id
 * @property string $title
 *
 * @property CoworkerProperty[] $coworkerProperties
 * @property Coworker[] $coworkers
 * @property Dimension[] $dimensions
 * @property Filter[] $filters
 * @property MaterialProperty[] $materialProperties
 * @property Material[] $materials
 * @property PropertyDimension[] $propertyDimensions
 * @property TechniqueProperty[] $techniqueProperties
 * @property Technique[] $techniques
 */
class Property extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'property';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function fields()
    {
        return [
            'id',
            'title',
            'dimensions' => function (Property $model) {
                return $model->dimensions;
            }
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
        ];
    }

    /**
     * Gets query for [[CoworkerProperties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCoworkerProperties()
    {
        return $this->hasMany(CoworkerProperty::class, ['property_id' => 'id']);
    }

    /**
     * Gets query for [[Coworkers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCoworkers()
    {
        return $this->hasMany(Coworker::class, ['id' => 'coworker_id'])->viaTable('coworker_property', ['property_id' => 'id']);
    }

    /**
     * Gets query for [[Dimensions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDimensions()
    {
        return $this->hasMany(Dimension::class, ['id' => 'dimension_id'])->viaTable('property_dimension', ['property_id' => 'id']);
    }

    /**
     * Gets query for [[Filters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFilters()
    {
        return $this->hasMany(Filter::class, ['property_id' => 'id']);
    }

    /**
     * Gets query for [[MaterialProperties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMaterialProperties()
    {
        return $this->hasMany(MaterialProperty::class, ['property_id' => 'id']);
    }

    /**
     * Gets query for [[Materials]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMaterials()
    {
        return $this->hasMany(Material::class, ['id' => 'material_id'])->viaTable('material_property', ['property_id' => 'id']);
    }

    /**
     * Gets query for [[PropertyDimensions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPropertyDimensions()
    {
        return $this->hasMany(PropertyDimension::class, ['property_id' => 'id']);
    }

    /**
     * Gets query for [[TechniqueProperties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTechniqueProperties()
    {
        return $this->hasMany(TechniqueProperty::class, ['property_id' => 'id']);
    }

    /**
     * Gets query for [[Techniques]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTechniques()
    {
        return $this->hasMany(Technique::class, ['id' => 'technique_id'])->viaTable('technique_property', ['property_id' => 'id']);
    }
}
