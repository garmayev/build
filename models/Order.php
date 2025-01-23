<?php

namespace app\models;

use app\models\telegram\TelegramMessage;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\web\UploadedFile;

/**
 * This is the model class for table "order".
 *
 * @property int $id
 * @property int|null $status
 * @property int|null $building_id
 * @property int|null $date
 * @property int|null $type
 * @property string $comment
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
 * @property Attachment[] $attachments
 */
class Order extends \yii\db\ActiveRecord
{
    public $datetime;
    public $files;

    const STATUS_NEW = 0;
    const STATUS_PROCESS = 1;
    const STATUS_BUILD = 2;
    const STATUS_COMPLETE = 3;

    const TYPE_COWORKER = 1;
    const TYPE_MATERIAL = 2;
    const TYPE_TECHNIQUE = 3;

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false
            ]
        ];
    }

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
        foreach ($this->telegramMessages as $message) {
            $message->remove();
        }
        return parent::beforeDelete();
    }

    public function beforeValidate()
    {
        $this->date = \Yii::$app->formatter->asTimestamp($this->datetime);
        $this->attachments = $this->files;
        return parent::beforeValidate();
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['status', 'building_id', 'date', 'type'], 'integer'],
            [['building_id'], 'exist', 'skipOnError' => true, 'targetClass' => Building::class, 'targetAttribute' => ['building_id' => 'id']],
            [['comment'], 'string'],
            [['filters', 'datetime', 'attachments'], 'safe'],
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
            'typeName' => Yii::t('app', 'Order Type'),
            'comment' => Yii::t('app', 'Comment'),
            'attachments' => Yii::t('app', 'Attachments'),
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

    public function getAttachments()
    {
        return $this->hasMany(Attachment::class, ['target_id' => 'id']);
    }

    public function setAttachments($data)
    {
        $this->save(false);
        foreach ($this->files as $item) {
            $attach = new Attachment();
            $attach->file = $item;
            $attach->target_class = Order::className();
            if ($attach->upload() && $attach->save()) {
                $this->link('attachments', $attach, ['target_class' => Order::className()]);
            }
        }
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

    public function getTypeName($type = null)
    {
        $list = [
            Order::TYPE_COWORKER => \Yii::t('app', 'Coworker'),
            Order::TYPE_MATERIAL => \Yii::t('app', 'Material'),
            Order::TYPE_TECHNIQUE => \Yii::t('app', 'Technique')
        ];
        if (isset($type)) {
            return $list[$type];
        }
        return $list[$this->type];
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

    public function getTelegramMessages()
    {
        return $this->hasMany(TelegramMessage::class, ['order_id' => 'id']);
    }

    public function filterCoworker()
    {
        $query = Coworker::find()->where(['priority' => Coworker::PRIORITY_HIGH]);
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
                        'text' => $this->generateTelegramText(\Yii::t('app', 'New Order').' #'.$this->id),
                        'chat_id' => $item->chat_id,
                        'order_id' => $this->id,
                        'status' => TelegramMessage::STATUS_NEW,
                        'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [
                                    ["text" => \Yii::t("app", "Agree"), "callback_data" => "order_id={$this->id}&action=ok"]
                                ], [
                                    ["text" => \Yii::t("app", "Disagree"), "callback_data" => "order_id={$this->id}&action=cancel"]
                                ]
                            ]
                        ])
                    ]
                ];
                if ($message->load($data) && $message->save()) {
//                    $message->send();
//                    $result[$user->chat_id] = $message->send();
                }
            } else if ($user->device_id) {
                \Yii::error('Push notification');
            }
        }
        return $result;
    }

    /**
     * @throws InvalidConfigException
     */
    public function check()
    {
        // TODO: Реализовать проверку соответствия сотрудника и требований к заказу
        // TODO: Реализовать пул (список) согласившихся сотрудников
        // TODO: Через промежуток времени проверить "заполненность" заказа
        switch ( $this->type ) {
            case Order::TYPE_COWORKER:
                $f = $this->getFilters()->joinWith('orderFilters')->andWhere(['order_filter.order_id' => $this->id])->one();
                $count = OrderCoworker::find()->where(['order_id' => $this->id])->all();
                break;
        }
        return $f->count === count($count);
    }

    public function addCoworker($user)
    {
        $coworker = Coworker::findOne(['user_id' => $user->id]);
        $this->link('coworkers', $coworker);
    }

    public function generateTelegramText($header)
    {
        $building = $this->building;
        $location = $building->location;
        $result = "<b>$header</b>\n\n";
        $result .= \Yii::t('app', 'Building').": {$building->title}\n";
        $result .= \Yii::t('app', 'Address').": <a href='https://2gis.ru/geo/{$location->longitude}%2C{$location->latitude}?m={$location->longitude}%2C{$location->latitude}%2F14'>{$location->address}</a>\n";
        $result .= \Yii::t('app', 'Date').": ".\Yii::$app->formatter->asDate($this->date)."\n";
        $result .= \Yii::t('app', 'Requirement').":\n";
        foreach ($this->filters as $filter) {
            $category = $filter->category;
            $result .= "\t\t\t\t{$category->title}: {$filter->count}\n";
            foreach ($filter->requirements as $requirement) {
                $result .= "\t\t\t\t\t\t\t\t{$requirement->property->title} {$requirement->type} {$requirement->value} {$requirement->dimension->title}\n";
            }
        }
        $result .= \Yii::t('app', 'Comment').": ".$this->comment."\n";
        $result .= \Yii::t('app', 'Attachments').":\n";
        foreach ($this->attachments as $attach) {
            $result .= \yii\helpers\Url::to($attach->url, true)."\n";
        }
        return $result;
    }
}
