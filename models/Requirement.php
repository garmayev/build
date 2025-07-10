<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "requirement".
 *
 * @property int $id
 * @property int|null $property_id
 * @property int|null $dimension_id
 * @property int|null $order_id
 * @property int|null $category_id
 * @property float|null $value
 * @property string|null $type
 * @property int|null $filter_id
 *
 * @property Dimension $dimension
 * @property Property $property
 * @property Order $order
 * @property Category $category
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
            [['property_id', 'dimension_id', 'order_id', 'category_id'], 'integer'],
            [['value'], 'number'],
            [['type'], 'string', 'max' => 255],
            [['dimension_id'], 'exist', 'skipOnError' => true, 'targetClass' => Dimension::class, 'targetAttribute' => ['dimension_id' => 'id']],
            [['property_id'], 'exist', 'skipOnError' => true, 'targetClass' => Property::class, 'targetAttribute' => ['property_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['dimension', 'property', 'category', 'order'], 'safe']
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
            'count' => Yii::t('app', 'Count'),
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

    /**
     * Gets query for [[Property]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProperty()
    {
        return $this->hasOne(Property::class, ['id' => 'property_id']);
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function getCoworkers()
    {
        $query = Coworker::find()
            ->select('filter.*, user.*')
            ->where(['filter.id' => $this->filter_id])
            ->andWhere(['filter.category_id' => 'coworker.category_id'])
            ->andWhere(['property_id' => $this->property_id]);
        switch (strtolower($this->type)) {
            case 'Less':
                $query->andWhere(['<=', 'value', $this->value]);
                break;
            case 'More':
                $query->andWhere(['>=', 'value', $this->value]);
                break;
            case 'Equal':
                $query->andWhere(['=', 'value', $this->value]);
                break;
            case 'Not Equal':
                $query->andWhere(['<>', 'value', $this->value]);
                break;
        }
        \Yii::error( $query->createCommand()->getRawSql() );
        \Yii::error( count($query->all()) );
        return $query->all();
    }
}
