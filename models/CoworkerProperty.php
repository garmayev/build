<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "coworker_property".
 *
 * @property int $coworker_id
 * @property int $property_id
 *
 * @property Coworker $coworker
 * @property Property $property
 */
class CoworkerProperty extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'coworker_property';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coworker_id', 'property_id'], 'required'],
            [['coworker_id', 'property_id'], 'integer'],
            [['coworker_id', 'property_id'], 'unique', 'targetAttribute' => ['coworker_id', 'property_id']],
            [['coworker_id'], 'exist', 'skipOnError' => true, 'targetClass' => Coworker::class, 'targetAttribute' => ['coworker_id' => 'id']],
            [['property_id'], 'exist', 'skipOnError' => true, 'targetClass' => Property::class, 'targetAttribute' => ['property_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'coworker_id' => Yii::t('app', 'Coworker ID'),
            'property_id' => Yii::t('app', 'Property ID'),
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
     * Gets query for [[Property]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProperty()
    {
        return $this->hasOne(Property::class, ['id' => 'property_id']);
    }
}
