<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "category_coworker".
 *
 * @property int $category_id
 * @property int $coworker_id
 *
 * @property Category $category
 * @property Coworker $coworker
 */
class CategoryCoworker extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category_coworker';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'coworker_id'], 'required'],
            [['category_id', 'coworker_id'], 'integer'],
            [['category_id', 'coworker_id'], 'unique', 'targetAttribute' => ['category_id', 'coworker_id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['coworker_id'], 'exist', 'skipOnError' => true, 'targetClass' => Coworker::class, 'targetAttribute' => ['coworker_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'category_id' => Yii::t('app', 'Category ID'),
            'coworker_id' => Yii::t('app', 'Coworker ID'),
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
     * Gets query for [[Coworker]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCoworker()
    {
        return $this->hasOne(Coworker::class, ['id' => 'coworker_id']);
    }
}
