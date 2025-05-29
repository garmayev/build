<?php

namespace app\models;

// session_start();

use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
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
     * Cache duration in seconds
     */
    const CACHE_DURATION = 3600; // 1 hour

    /**
     * Returns the table name for this model
     *
     * @return string The table name
     */
    public static function tableName()
    {
        return 'filter';
    }

    /**
     * Defines validation rules for model attributes
     *
     * @return array Array of validation rules
     */
    public function rules()
    {
        return [
            [['category_id', 'count'], 'integer'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['requirements'], 'safe'],
        ];
    }

    /**
     * Defines which fields should be exposed in API responses
     *
     * @return array Array of fields and their formatters
     */
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
     * Defines attribute labels for the model
     *
     * @return array Array of attribute labels
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

    /**
     * Handles operations before deleting the model
     * Deletes all related requirements within a transaction
     *
     * @return bool Whether the deletion should continue
     * @throws \Exception if deletion fails
     */
    public function beforeDelete()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->requirements as $requirement) {
                $requirement->delete();
            }
            $transaction->commit();
            return parent::beforeDelete();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error deleting filter requirements: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gets the related Category model
     *
     * @return ActiveQuery Query for the related Category
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Sets the category_id from provided data
     *
     * @param array $data Array containing category data with 'id' key
     */
    public function setCategory($data)
    {
        if (isset($data['id'])) {
            $this->category_id = $data['id'];
        }
    }

    /**
     * Gets the related Dimension model
     *
     * @return ActiveQuery Query for the related Dimension
     */
    public function getDimension()
    {
        return $this->hasOne(Dimension::class, ['id' => 'dimension_id']);
    }

    /**
     * Gets related OrderFilter models
     *
     * @return ActiveQuery Query for related OrderFilters
     */
    public function getOrderFilters()
    {
        return $this->hasMany(OrderFilter::class, ['filter_id' => 'id']);
    }

    /**
     * Gets related Order models through order_filter table
     *
     * @return ActiveQuery Query for related Orders
     * @throws InvalidConfigException if the configuration is invalid
     */
    public function getOrders(): ActiveQuery
    {
        return $this->hasMany(Order::class, ['id' => 'order_id'])
            ->viaTable('order_filter', ['filter_id' => 'id']);
    }

    /**
     * Gets the related Property model
     *
     * @return ActiveQuery Query for the related Property
     */
    public function getProperty()
    {
        return $this->hasOne(Property::class, ['id' => 'property_id']);
    }

    /**
     * Gets related Requirement models
     *
     * @return ActiveQuery Query for related Requirements
     */
    public function getRequirements()
    {
        return $this->hasMany(Requirement::class, ['filter_id' => 'id']);
    }

    /**
     * Sets requirements for the filter within a transaction
     * Deletes existing requirements and creates new ones
     *
     * @param array $data Array of requirement data
     * @throws \Exception if setting requirements fails
     */
    public function setRequirements($data)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->save(false);
            foreach ($this->requirements as $requirement) {
                $this->unlink('requirements', $requirement, true);
            }
            if ($data) {
                foreach ($data as $item) {
//                    \Yii::error($item);
                    $req = new Requirement();
                    if ($req->load(['Requirement' => $item]) && $req->save()) {
                        $this->link('requirements', $req);
                    } else {
                        Yii::error('Failed to save requirement: ' . json_encode($req->getErrorSummary(true)));
                    }
                }
            }
            $transaction->commit();
            TagDependency::invalidate(Yii::$app->cache, ['filter-' . $this->id]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error setting requirements: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gets coworkers matching the filter criteria with specified priority
     *
     * @param int $priority Priority level for filtering coworkers
     * @return ActiveQuery Query for matching coworkers
     */
    public function getCoworkers($priority = Coworker::PRIORITY_HIGH)
    {
        $query = Coworker::find()
            ->joinWith('properties')
            ->where(['coworker.priority' => $priority, 'coworker.category_id' => $this->category_id]);
        return $this->extracted($query);
    }

    /**
     * Gets detailed information about filter application to an order
     *
     * @param int $order_id ID of the order
     * @param int $priority Priority level for filtering
     * @param array $exclude Array of coworkers to exclude
     * @return array|null Details about filter application
     */
    public function details($order_id, $priority = Coworker::PRIORITY_HIGH, $exclude = [])
    {
        $model = Order::findOne($order_id);
        if (!$model) {
            return null;
        }

        $agree = $model->countCoworkersByFilter($this);
        if (empty($exclude)) {
            $exclude = $model->coworkers;
        }

        return [
            "agree" => $agree,
            "needle" => $this->count - $agree,
            "coworkers" => $this->findCoworkers($priority > -1 ? $priority : 0),
        ];
    }

    /**
     * Finds coworkers matching the filter criteria with specific priority
     *
     * @param int $priority Priority level for filtering
     * @return array Array of matching Coworker models
     */
    public function findCoworkers($priority)
    {
        $cacheKey = 'filter-find-coworkers-' . $this->id . '-' . $priority;
        $query = Coworker::find()
            ->joinWith('properties')
            ->where([
                'coworker.category_id' => $this->category_id,
                'coworker.priority' => $priority,
                'coworker.created_by' => isset(\Yii::$app->user) ? \Yii::$app->user->id : 1,
            ]);
        $resultQuery = $this->extracted($query);
        return $resultQuery->all();
    }

    /**
     * Applies requirement conditions to the query
     * Helper method for filtering coworkers based on requirements
     *
     * @param ActiveQuery $query Base query to apply conditions to
     * @return ActiveQuery Modified query with applied conditions
     */
    protected function extracted(ActiveQuery $query): ActiveQuery
    {
        foreach ($this->requirements as $requirement) {
            $query->andWhere(['coworker_property.property_id' => $requirement->property_id]);
            
            $value = $requirement->value;
            switch ($requirement->type) {
                case Yii::t('app', 'Less'):
                    $query->andWhere(['<=', 'coworker_property.value', $value]);
                    break;
                case Yii::t('app', 'More'):
                    $query->andWhere(['>=', 'coworker_property.value', $value]);
                    break;
                case Yii::t('app', 'Equal'):
                    $query->andWhere(['=', 'coworker_property.value', $value]);
                    break;
                case Yii::t('app', 'Not Equal'):
                    $query->andWhere(['<>', 'coworker_property.value', $value]);
                    break;
            }
        }
        return $query;
    }
}
