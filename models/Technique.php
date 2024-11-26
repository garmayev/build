<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "technique".
 *
 * @property int $id
 * @property string $title
 * @property int|null $coworker_id
 *
 * @property Coworker $coworker
 * @property OrderTechnique[] $orderTechniques
 * @property Order[] $orders
 * @property Property[] $properties
 * @property TechniqueProperty[] $techniqueProperties
 */
class Technique extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'technique';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['coworker_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['coworker_id'], 'exist', 'skipOnError' => true, 'targetClass' => Coworker::class, 'targetAttribute' => ['coworker_id' => 'id']],
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
            'coworker_id' => Yii::t('app', 'Coworker ID'),
        ];
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

    /**
     * Gets query for [[OrderTechniques]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderTechniques()
    {
        return $this->hasMany(OrderTechnique::class, ['technique_id' => 'id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['id' => 'order_id'])->viaTable('order_technique', ['technique_id' => 'id']);
    }

    /**
     * Gets query for [[Properties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProperties()
    {
        return $this->hasMany(Property::class, ['id' => 'property_id'])->viaTable('technique_property', ['technique_id' => 'id']);
    }

    /**
     * Gets query for [[TechniqueProperties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTechniqueProperties()
    {
        return $this->hasMany(TechniqueProperty::class, ['technique_id' => 'id']);
    }
}
