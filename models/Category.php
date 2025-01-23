<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property string $title
 * @property int $type
 * @property int|null $parent_id
 *
 * @property Category[] $categories
 * @property CategoryCoworker[] $categoryCoworkers
 * @property CategoryMaterial[] $categoryMaterials
 * @property CategoryProperty[] $categoryProperties
 * @property CategoryTechnique[] $categoryTechniques
 * @property Coworker[] $coworkers
 * @property Coworker[] $coworkers0
 * @property Filter[] $filters
 * @property Material[] $materials
 * @property Category $parent
 * @property Property[] $properties
 * @property Technique[] $techniques
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'type'], 'required'],
            [['type', 'parent_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['parent_id' => 'id']],
            [['properties'], 'safe']
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
            'type' => Yii::t('app', 'Type'),
            'parent_id' => Yii::t('app', 'Parent ID'),
        ];
    }

    /**
     * Gets query for [[Categories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::class, ['parent_id' => 'id']);
    }

    /**
     * Gets query for [[CategoryCoworkers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryCoworkers()
    {
        return $this->hasMany(CategoryCoworker::class, ['category_id' => 'id']);
    }

    /**
     * Gets query for [[CategoryMaterials]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryMaterials()
    {
        return $this->hasMany(CategoryMaterial::class, ['category_id' => 'id']);
    }

    /**
     * Gets query for [[CategoryProperties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryProperties()
    {
        return $this->hasMany(CategoryProperty::class, ['category_id' => 'id']);
    }

    /**
     * Gets query for [[CategoryTechniques]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryTechniques()
    {
        return $this->hasMany(CategoryTechnique::class, ['category_id' => 'id']);
    }

    /**
     * Gets query for [[Coworkers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCoworkers()
    {
        return $this->hasMany(Coworker::class, ['category_id' => 'id']);
    }

    /**
     * Gets query for [[Coworkers0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCoworkers0()
    {
        return $this->hasMany(Coworker::class, ['id' => 'coworker_id'])->viaTable('category_coworker', ['category_id' => 'id']);
    }

    /**
     * Gets query for [[Filters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFilters()
    {
        return $this->hasMany(Filter::class, ['category_id' => 'id']);
    }

    /**
     * Gets query for [[Materials0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMaterials()
    {
        return $this->hasMany(Material::class, ['id' => 'material_id'])->viaTable('category_material', ['category_id' => 'id']);
    }

    /**
     * Gets query for [[Parent]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Category::class, ['id' => 'parent_id']);
    }

    /**
     * Gets query for [[Properties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProperties()
    {
        return $this->hasMany(Property::class, ['id' => 'property_id'])->viaTable('category_property', ['category_id' => 'id']);
    }

    public function setProperties($data)
    {
        $this->save(false);
        foreach ( $this->properties as $property ) $this->unlink('properties', $property, true);
        foreach ( $data as $item ) {
            $property = Property::findOne($item);
            $this->link('properties', $property);
        }
    }

    /**
     * Gets query for [[Techniques]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTechniques()
    {
        return $this->hasMany(Technique::class, ['id' => 'technique_id'])->viaTable('category_technique', ['category_id' => 'id']);
    }
}
