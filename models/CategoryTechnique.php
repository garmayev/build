<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "category_technique".
 *
 * @property int $category_id
 * @property int $technique_id
 *
 * @property Category $category
 * @property Technique $technique
 */
class CategoryTechnique extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category_technique';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'technique_id'], 'required'],
            [['category_id', 'technique_id'], 'integer'],
            [['category_id', 'technique_id'], 'unique', 'targetAttribute' => ['category_id', 'technique_id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['technique_id'], 'exist', 'skipOnError' => true, 'targetClass' => Technique::class, 'targetAttribute' => ['technique_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'category_id' => Yii::t('app', 'Category ID'),
            'technique_id' => Yii::t('app', 'Technique ID'),
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
     * Gets query for [[Technique]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTechnique()
    {
        return $this->hasOne(Technique::class, ['id' => 'technique_id']);
    }
}
