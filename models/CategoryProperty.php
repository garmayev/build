<?php
namespace app\models;

/*

 */
class CategoryProperty extends \yii\db\ActiveRecord 
{
    public static function tableName()
    {
        return 'category_property';
    }

    public function rules()
    {
        return [
            [['category_id', 'property_id'], 'required'],
            [['category_id', 'property_id'], 'integer'],
            [['category_id', 'property_id'], 'unique', 'targetAttribute' => ['category_id', 'property_id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['property_id'], 'exist', 'skipOnError' => true, 'targetClass' => Property::class, 'targetAttribute' => ['property_id' => 'id']]
        ];
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getProperty()
    {
        return $this->hasOne(Property::class, ['id' => 'property_id']);
    }
}