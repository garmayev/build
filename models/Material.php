<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "material".
 *
 * @property int $id
 * @property string $title
 * @property float|null $price
 * @property string|null $image
 * @property int $category_id
 *
 * @property Category $category
 * @property MaterialProperty[] $materialProperties
 * @property OrderMaterial[] $orderMaterials
 * @property Order[] $orders
 * @property Property[] $properties
 */
class Material extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'material';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'category_id'], 'required'],
            [['price'], 'number'],
            [['category_id'], 'integer'],
            [['title', 'image'], 'string', 'max' => 255],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
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
            'price' => Yii::t('app', 'Price'),
            'image' => Yii::t('app', 'Image'),
            'category_id' => Yii::t('app', 'Category ID'),
        ];
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[MaterialProperties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMaterialProperties()
    {
        return $this->hasMany(MaterialProperty::class, ['material_id' => 'id']);
    }

    /**
     * Gets query for [[OrderMaterials]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderMaterials()
    {
        return $this->hasMany(OrderMaterial::class, ['material_id' => 'id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['id' => 'order_id'])->viaTable('order_material', ['material_id' => 'id']);
    }

    /**
     * Gets query for [[Properties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProperties()
    {
        return $this->hasMany(Property::class, ['id' => 'property_id'])->viaTable('material_property', ['material_id' => 'id']);
    }
}
