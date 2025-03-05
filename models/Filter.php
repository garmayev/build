<?php

namespace app\models;

// session_start();

use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "filter".
 *
 * @property int $id
 * @property int|null $category_id
 * @property int|null $property_id
 * @property int|null $dimension_id
 * @property int|null $count
 *
 * @property Category $category
 * @property Dimension $dimension
 * @property OrderFilter[] $orderFilters
 * @property Order[] $orders
 * @property Property $property
 * @property Requirement[] $requirements
 * @property Coworker[] $coworkers
 */
class Filter extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'filter';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'count'], 'integer'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['requirements'], 'safe'],
        ];
    }

    public function fields()
    {
        return [
            'id',
            'count',
            'category' => function ($model) {
                return $model->category;
            },
            'requirements'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category_id' => Yii::t('app', 'Category ID'),
            'property_id' => Yii::t('app', 'Property ID'),
            'dimension_id' => Yii::t('app', 'Dimension ID'),
            'count' => Yii::t('app', 'Count'),
        ];
    }

    public function beforeDelete()
    {
        foreach ($this->requirements as $requirement) {
            $requirement->delete();
        }
        return parent::beforeDelete();
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

    public function setCategory($data)
    {
        $this->category_id = $data['id'];
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
     * Gets query for [[OrderFilters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderFilters()
    {
        return $this->hasMany(OrderFilter::class, ['filter_id' => 'id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     * @throws InvalidConfigException
     */
    public function getOrders(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Order::class, ['id' => 'order_id'])->viaTable('order_filter', ['filter_id' => 'id']);
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

    public function getRequirements()
    {
        return $this->hasMany(Requirement::class, ['filter_id' => 'id']);
    }

    public function setRequirements($data)
    {
        $this->save(false);
        foreach ($this->requirements as $requirement) {
            $this->unlink('requirements', $requirement, true);
        }
        foreach ($data as $item) {
            $req = new Requirement();
            if ($req->load(['Requirement' => $item]) && $req->save()) {
                $this->link('requirements', $req);
            } else {
                \Yii::error('Requirement is not saved');
            }
        }
    }

    public function getCoworkers($priority = Coworker::PRIORITY_HIGH)
    {
        $query = Coworker::find()->joinWith('properties')->where(['priority' => $priority])->andWhere(['user_id' => 1]);
        $query->andWhere(['category_id' => $this->category_id]);
        foreach ($this->requirements as $requirement) {
            $query->andWhere(['property.id' => $requirement->property_id]);
            switch ($requirement->type) {
                case \Yii::t('app', 'Less'):
                    $query->andWhere(['<=', 'coworker_property.value', $requirement->value]);
                    break;
                case \Yii::t('app', 'More'):
                    $query->andWhere(['>=', 'coworker_property.value', $requirement->value]);
                    break;
                case \Yii::t('app', 'Equal'):
                    $query->andWhere(['=', 'coworker_property.value', $requirement->value]);
                    break;
                case \Yii::t('app', 'Not Equal'):
                    $query->andWhere(['<>', 'coworker_property.value', $requirement->value]);
                    break;
            }
        }
        return $query;
    }

    public function details($order_id, $priority = Coworker::PRIORITY_HIGH, $exclude = [])
    {
        $model = Order::findOne($order_id);
        $agree = $model->countCoworkersByFilter($this);
        if (count($exclude) === 0) {
            $exclude = $model->coworkers;
        }
        return [
            "agree" => $agree,
            "needle" => $this->count - $agree,
            "coworkers" => $this->findCoworkers($priority),
        ];
    }

    public function findCoworkers($priority)
    {
        $query = Coworker::find()
            ->joinWith('properties')
            ->where(['coworker.category_id' => $this->category_id]);
        foreach ($this->requirements as $requirement) {
            $query->andWhere(['property.id' => $requirement->property_id]);
            switch ($requirement->type) {
                case \Yii::t('app', 'Less'):
                    $query->andWhere(['<=', 'coworker_property.value', $requirement->value]);
                    break;
                case \Yii::t('app', 'More'):
                    $query->andWhere(['>=', 'coworker_property.value', $requirement->value]);
                    break;
                case \Yii::t('app', 'Equal'):
                    $query->andWhere(['=', 'coworker_property.value', $requirement->value]);
                    break;
                case \Yii::t('app', 'Not Equal'):
                    $query->andWhere(['<>', 'coworker_property.value', $requirement->value]);
                    break;
            }
        }
//        echo "Filter: Priority = " . $priority . "\n";
        $query->andWhere(['coworker.priority' => $priority]);
        if (isset($_SESSION["__id"])) $query->andWhere(['user_id' => $_SESSION['__id']]);
//        echo $query->createCommand()->getRawSql()."\n\n";
        return $query->all();
    }
}
