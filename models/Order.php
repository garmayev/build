<?php

namespace app\models;

use app\models\telegram\TelegramMessage;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "order".
 *
 * @property int $id
 * @property int|null $status
 * @property int|null $building_id
 * @property int|null $date
 * @property int|null $type
 *
 * @property string $statusTitle
 * @property array $statusList
 *
 * @property Building $building
 * @property Coworker[] $coworkers
 * @property Filter[] $filters
 * @property Material[] $materials
 * @property OrderCoworker[] $orderCoworkers
 * @property OrderFilter[] $orderFilters
 * @property OrderMaterial[] $orderMaterials
 * @property OrderTechnique[] $orderTechniques
 * @property Technique[] $techniques
 */
class Order extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_PROCESS = 1;
    const STATUS_BUILD = 2;
    const STATUS_COMPLETE = 3;

    const TYPE_COWORKER = 1;
    const TYPE_MATERIAL = 2;
    const TYPE_TECHNIQUE = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'order';
    }

    public function beforeDelete()
    {
        foreach ($this->filters as $filter) {
            $filter->delete();
        }
        return parent::beforeDelete();
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $this->notify();
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['status', 'building_id', 'date', 'type'], 'integer'],
            [['building_id'], 'exist', 'skipOnError' => true, 'targetClass' => Building::class, 'targetAttribute' => ['building_id' => 'id']],
            [['filters'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'status' => Yii::t('app', 'Status'),
            'building_id' => Yii::t('app', 'Building ID'),
            'date' => Yii::t('app', 'Date'),
        ];
    }

    /**
     * Gets query for [[Building]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBuilding(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Building::class, ['id' => 'building_id']);
    }

    /**
     * Gets query for [[Coworkers]].
     *
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getCoworkers(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Coworker::class, ['id' => 'coworker_id'])->viaTable('order_coworker', ['order_id' => 'id']);
    }

    /**
     * Gets query for [[Filters]].
     *
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getFilters(): ActiveQuery
    {
        return $this->hasMany(Filter::class, ['id' => 'filter_id'])->viaTable('order_filter', ['order_id' => 'id']);
    }

    public function getStatusTitle()
    {
        $list = [
            Order::STATUS_NEW => \Yii::t('app', 'New Order'),
            Order::STATUS_PROCESS => \Yii::t('app', 'Order in process'),
            Order::STATUS_BUILD => \Yii::t('app', 'Order building'),
            Order::STATUS_COMPLETE => \Yii::t('app', 'Order completed'),
        ];
        return $list[$this->status];
    }

    public function getStatusList(): array
    {
        return [
            Order::STATUS_NEW => \Yii::t('app', 'New Order'),
            Order::STATUS_PROCESS => \Yii::t('app', 'Order in process'),
            Order::STATUS_BUILD => \Yii::t('app', 'Order building'),
            Order::STATUS_COMPLETE => \Yii::t('app', 'Order completed'),
        ];
    }

    public function setFilters($data)
    {
        $this->save(false);
        foreach ($this->filters as $filter) {
            $filter->unlink('filters', $filter, true);
        }
        foreach ($data as $item) {
            $filter = new Filter();
            if ($filter->load(['Filter' => $item]) && $filter->save()) {
                $this->link('filters', $filter);
            } else {
                \Yii::error('Filter not saved');
            }
        }
    }

    /**
     * Gets query for [[Materials]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMaterials()
    {
        return $this->hasMany(Material::class, ['id' => 'material_id'])->viaTable('order_material', ['order_id' => 'id']);
    }

    /**
     * Gets query for [[OrderCoworkers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderCoworkers()
    {
        return $this->hasMany(OrderCoworker::class, ['order_id' => 'id']);
    }

    /**
     * Gets query for [[OrderFilters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderFilters()
    {
        return $this->hasMany(OrderFilter::class, ['order_id' => 'id']);
    }

    /**
     * Gets query for [[OrderMaterials]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderMaterials()
    {
        return $this->hasMany(OrderMaterial::class, ['order_id' => 'id']);
    }

    /**
     * Gets query for [[OrderTechniques]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderTechniques()
    {
        return $this->hasMany(OrderTechnique::class, ['order_id' => 'id']);
    }

    /**
     * Gets query for [[Techniques]].
     *
     * @return \yii\db\ActiveQuery
     * @throws InvalidConfigException
     */
    public function getTechniques()
    {
        return $this->hasMany(Technique::class, ['id' => 'technique_id'])->viaTable('order_technique', ['order_id' => 'id']);
    }

    public function filter()
    {
        switch ($this->type) {
            case Order::TYPE_COWORKER:
                $query = $this->filterCoworker()->createCommand()->queryAll();
                break;
            case Order::TYPE_MATERIAL:
                $query = Material::find();
                break;
            default:
                $query = Technique::find();
                break;
        }
        return $query;
    }

    public function filterCoworker()
    {
        $query = Coworker::find();
        $query->joinWith('properties');
        foreach ($this->filters as $filter) {
            $query->where(['category_id' => $filter->category_id]);
            foreach ($filter->requirements as $requirement) {
                $query->andWhere(['property.id' => $requirement->property_id]);
                switch ($requirement->type) {
                    case \Yii::t('app', 'Less'):
                        $query->andWhere(['<', 'coworker_property.value', $requirement->value]);
                        break;
                    case \Yii::t('app', 'More'):
                        $query->andWhere(['>', 'coworker_property.value', $requirement->value]);
                        break;
                    case \Yii::t('app', 'Equal'):
                        $query->andWhere(['=', 'coworker_property.value', $requirement->value]);
                        break;
                    case \Yii::t('app', 'Not Equal'):
                        $query->andWhere(['<>', 'coworker_property.value', $requirement->value]);
                        break;
                }
            }
        }
        return $query;
    }

    public function notify()
    {
        $data = $this->filter();
        $result = [];
        foreach ($data as $item) {
            $user = User::findOne($item['user_id']);
            if ($user->chat_id) {
                $message = new TelegramMessage();
                $data = [
                    'TelegramMessage' => [
                        'text' => "Order #$this->id",
                        'chat_id' => $user->chat_id,
                        'reply_markup' => json_encode([
                            'inline_markup' => [
                                [
                                    ["text" => "Ok", "callback_data" => "order_id={$this->id}&action=ok"]
                                ], [
                                    ["text" => "Cancel", "callback_data" => "order_id={$this->id}&action=cancel"]
                                ]
                            ]
                        ])
                    ]
                ];
                if ($message->load($data) && $message->save()) {
                    $result[$user->chat_id] = $message->send();
                }
            } else if ($user->device_id) {
                \Yii::error('Push notification');
            }
        }
        return $result;
    }

    public function check($target) 
    {
        switch ( $this->type ) {
            case Order::TYPE_COWORKER:
                $query = Coworker::find()->where(['user_id' => $target->id])->andWhere(['category_id' => \yii\helpers\ArrayHelper::map($this->filters, 'category_id', 'category_id')])->all();
                $count = OrderCoworker::find()->where(['order_id' => $this->id])->all();
                break;
        }
        return count($query) === count($count) ;
    }

    public function addCoworker($user) 
    {
        $coworker = Coworker::findOne(['user_id' => $user->id]);
        $this->link('coworkers', $coworker);
    }
}
