<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "property".
 *
 * @property int $id
 * @property string $title
 *
 * @property UserProperty[] $userProperties
 * @property Property[] $properties
 * @property Dimension[] $dimensions
 * @property Filter[] $filters
 * @property PropertyDimension[] $propertyDimensions
 * @property TechniqueProperty[] $techniqueProperties
 * @property Technique[] $techniques
 */
class Property extends \yii\db\ActiveRecord
{
    public $dimension_ids;

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
    public function rules(): array
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['dimensions'], 'safe']
        ];
    }

    public function fields(): array
    {
        return [
            'id',
            'title',
            'dimensions'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'dimensions' => Yii::t('app', 'Dimensions'),
        ];
    }

    /**
     * Gets query for [[CoworkerProperties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserProperties(): \yii\db\ActiveQuery
    {
        return $this->hasMany(UserProperty::class, ['property_id' => 'id']);
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

    public function setDimensions($data)
    {
        if ($data) {
            if ($this->isNewRecord) {
                $this->save(false);
            } else {
                foreach ($this->dimensions as $dimension) $this->unlink('dimensions', $dimension, true);
            }
            foreach ($data as $item) {
                $dimension = Dimension::findOne($item);
                $this->link('dimensions', $dimension);
            }
        }
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
