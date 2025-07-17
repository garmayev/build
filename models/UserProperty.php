<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $user_id
 * @property int $property_id
 * @property int $category_id
 * @property int $dimension_id
 * @property int $value
 *
 * @property User $user
 * @property Property $property
 * @property Category $category
 * @property Dimension $dimension
 */
class UserProperty extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%user_property}}';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'property_id', 'dimension_id', 'category_id', 'value'], 'required'],
            [['user_id', 'property_id', 'dimension_id', 'category_id', 'value'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['property_id'], 'exist', 'skipOnError' => true, 'targetClass' => Property::className(), 'targetAttribute' => ['property_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['category_id' => 'id']],
            [['dimension_id'], 'exist', 'skipOnError' => true, 'targetClass' => Dimension::className(), 'targetAttribute' => ['dimension_id' => 'id']],
        ];
    }

    public function fields()
    {
        return [
            'property_id',
            'property',
            'dimension_id',
            'dimension',
            'category_id',
            'category',
            'value'
        ];
    }

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getProperty(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Property::className(), ['id' => 'property_id']);
    }

    public function getCategory(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    public function getDimension(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Dimension::className(), ['id' => 'dimension_id']);
    }
}