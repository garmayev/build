<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "requirement".
 *
 * @property int $id
 * @property int|null $property_id
 * @property int|null $dimension_id
 * @property float|null $value
 * @property string|null $type
 * @property int|null $filter_id
 *
 * @property Dimension $dimension
 * @property Filter $filter
 * @property Property $property
 */
class Requirement extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'requirement';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['property_id', 'dimension_id', 'filter_id'], 'integer'],
            [['value'], 'number'],
            [['type'], 'string', 'max' => 255],
            [['dimension_id'], 'exist', 'skipOnError' => true, 'targetClass' => Dimension::class, 'targetAttribute' => ['dimension_id' => 'id']],
            [['filter_id'], 'exist', 'skipOnError' => true, 'targetClass' => Filter::class, 'targetAttribute' => ['filter_id' => 'id']],
            [['property_id'], 'exist', 'skipOnError' => true, 'targetClass' => Property::class, 'targetAttribute' => ['property_id' => 'id']],
            [['dimension', 'property'], 'safe']
        ];
    }

    public function fields()
    {
        return [
            'property' => function (Requirement $model) {
                return $model->property;
            },
            'dimension' => function (Requirement $model) {
                return $model->dimension;
            },
            'value',
            'type',
            'coworkers' => function (Requirement $model) {
//                return $model->getCoworkers();
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'property_id' => Yii::t('app', 'Property ID'),
            'dimension_id' => Yii::t('app', 'Dimension ID'),
            'value' => Yii::t('app', 'Value'),
            'type' => Yii::t('app', 'Type'),
            'filter_id' => Yii::t('app', 'Filter ID'),
        ];
    }

    /**
     * Gets query for [[Dimension]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDimension()
    {
        return $this->hasOne(Dimension::class, ['id' => 'dimension_id']);
    }

    public function setDimension($data)
    {
        $this->dimension_id = $data['id'];
    }

    /**
     * Gets query for [[Filter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFilter()
    {
        return $this->hasOne(Filter::class, ['id' => 'filter_id']);
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

    public function setProperty($data)
    {
        $this->property_id = $data['id'];
    }

    public function getCoworkers()
    {
        $query = Coworker::find()
            ->select('filter.*, coworker.*')
//            ->joinWith('coworkerProperties')
//            ->leftJoin('filter', "filter.id = $this->filter_id")
            ->where(['filter.id' => $this->filter_id])
            ->andWhere(['filter.category_id' => 'coworker.category_id'])
            ->andWhere(['property_id' => $this->property_id]);
        switch ($this->type) {
            case \Yii::t('app', 'Less'):
                $query->andWhere(['<=', 'value', $this->value]);
                break;
            case \Yii::t('app', 'More'):
                $query->andWhere(['>=', 'value', $this->value]);
                break;
            case \Yii::t('app', 'Equal'):
                $query->andWhere(['=', 'value', $this->value]);
                break;
            case \Yii::t('app', 'Not Equal'):
                $query->andWhere(['<>', 'value', $this->value]);
                break;
        }
        \Yii::error( $query->createCommand()->getRawSql() );
        \Yii::error( count($query->all()) );
        return $query->all();
    }
}
