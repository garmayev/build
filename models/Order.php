<?php

namespace app\models;

use app\models\telegram\TelegramMessage;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\web\Linkable;
use yii\web\UploadedFile;
use ExpoSDK\Expo;
use ExpoSDK\ExpoMessage;

/**
 * This is the model class for table "order".
 *
 * @property int $id
 * @property int|null $status
 * @property int|null $building_id
 * @property int|null $date
 * @property int|null $type
 * @property string $comment
 * @property int $notify_stage
 * @property int $notify_date
 * @property int $created_by
 *
 * @property string $statusTitle
 * @property array $statusList
 * @property array $details
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
    public $files = [];

    const STATUS_NEW = 0;
    const STATUS_PROCESS = 1;
    const STATUS_BUILD = 2;
    const STATUS_COMPLETE = 3;

    const TYPE_COWORKER = 1;
    const TYPE_MATERIAL = 2;
    const TYPE_TECHNIQUE = 3;

    const EVENT_STATUS_UPDATE = 'statusUpdate';

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
            ], [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => false,
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
        $this->date = $this->date ?? \Yii::$app->formatter->asTimestamp($this->datetime);
        if (count($this->files)) {
            $this->attachments = $this->files;
        }
        return parent::beforeValidate();
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['status', 'building_id', 'date', 'type', 'notify_date', 'notify_stage', 'created_by', 'created_at', 'priority_level'], 'integer'],
            [['building_id'], 'exist', 'skipOnError' => true, 'targetClass' => Building::class, 'targetAttribute' => ['building_id' => 'id']],
            [['priority_level'], 'default', 'value' => Coworker::PRIORITY_HIGH],
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

    public function fields()
    {
        return [
            'id',
            'status' => function (Order $model) {
                return $model->statusTitle;
            },
            'type' => function (Order $model) {
                return $model->typeName;
            },
            'date',
            'comment',
            'building' => function (Order $model) {
                return $model->building;
            },
            'attachments' => function (Order $model) {
                return Attachment::find()->where(['target_class' => Order::class])->andWhere(['target_id' => $model->id])->all();
            },
            'filters' => function (Order $model) {
                return $model->filters;
            },
            'coworkers' => function (Order $model) {
                return $model->coworkers;
            },
            'hours',
            'needle' => function (Order $model) {

            }
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
        if (count($this->files)) {
            foreach ($this->files as $item) {
                $attach = new Attachment();
                $attach->file = $item;
                $attach->target_class = Order::className();
                if ($attach->upload() && $attach->save()) {
                    $this->link('attachments', $attach, ['target_class' => Order::className()]);
                }
            }
        } else if ($data) {
            foreach ($this->attachments as $attachment) {
                $this->unlink('attachments', $attachment, true);
            }
            foreach ($data as $item) {
                $attach = new Attachment();
                $attach->url = $item;
                $attach->target_class = Order::className();
                if ($attach->save()) {
                    $this->link('attachments', $attach, ['target_class' => Order::className()]);
                }
            }
        }
    }

    public function getHours()
    {
        return $this->hasMany(Hours::class, ['order_id' => 'id']);
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

    public function getFilters()
    {
        return $this->hasMany(Filter::class, ['id' => 'filter_id'])->viaTable('order_filter', ['order_id' => 'id']);
    }

    public function getTelegramMessages()
    {
        return $this->hasMany(TelegramMessage::class, ['order_id' => 'id']);
    }

    public function notify($priority = null)
    {
        foreach ($this->filters as $filter) {
            if (is_null($priority)) {
                $priority = Coworker::PRIORITY_HIGH;
            }
            $details = $filter->details($this->id, $priority);
//            echo "Needle: {$details['needle']}\n";
//            echo "Agree: {$details['agree']}\n";
//            echo count($details['coworkers']) . "\n";
            if ($priority >= Coworker::PRIORITY_LOW) {
                foreach ($details['coworkers'] as $coworker) {
                    echo "\tCoworker: $coworker->firstname $coworker->lastname\n";
                    if ($coworker->device_id) {
                        $message = new ExpoMessage([
                            "title" => \Yii::t("app", "New order") . " #{$this->id}",
                            "body" => $this->generateTelegramText(\Yii::t("app", "New order") . " #{$this->id}"),
                            "categoryId" => "new-order",
                            "data" => ["order_id" => $this->id]
                        ]);
                        $expo = new Expo();
                        $expo->send($message)->to($coworker->device_id)->push();
                    } else if ($coworker->chat_id) {
                        $coworker->sendMessage(
                            $this->generateTelegramText(\Yii::t('app', 'New order') . ' #' . $this->id),
                            json_encode([
                                'inline_keyboard' => [
                                    [
                                        ["text" => \Yii::t("app", "Agree"), "callback_data" => "/agree order_id={$this->id}&action=ok"]
                                    ], [
                                        ["text" => \Yii::t("app", "Disagree"), "callback_data" => "/refuse order_id={$this->id}&action=cancel"]
                                    ]
                                ]
                            ]),
                            $this->id
                        );
                    }
                }
                $this->notify_date = time();
                $this->notify_stage = $priority;
                $this->save();
            }
        }
    }

    /**
     * @throws InvalidConfigException
     */
    public function checkSuccessfully()
    {
        switch ($this->type) {
            case Order::TYPE_COWORKER:
                $count = 0;
                foreach ($this->filters as $filter) {
                    $count += $filter->count;
                }
                $f = $this->getFilters()->joinWith('orderFilters')->andWhere(['order_filter.order_id' => $this->id])->one();
                $count = OrderCoworker::find()->where(['order_id' => $this->id])->all();
                break;
        }
        if ( $f->count === count($count) ) {
            $this->trigger(self::EVENT_STATUS_UPDATE);
            return true;
        }
        return false;
    }

    public function countCoworkersByFilter($filter)
    {
        $coworkers = $filter->getCoworkers()->andWhere(['IN', 'coworker.id', ArrayHelper::map($this->coworkers, 'id', 'id')]);
        return $coworkers->count();
    }

    public function generateTelegramText($header)
    {
        $building = $this->building;
        $location = $building->location;
        $result = "<b>$header</b>\n\n";
        $result .= \Yii::t('app', 'Building') . ": {$building->title}\n";
        $result .= \Yii::t('app', 'Address') . ": {$location->link}\n";
        $result .= \Yii::t('app', 'Date') . ": " . \Yii::$app->formatter->asDate($this->date) . "\n";
        $result .= \Yii::t('app', 'Requirement') . ":\n";
        foreach ($this->filters as $filter) {
            $category = $filter->category;
            $count = $this->countCoworkersByFilter($filter);
            $result .= "\t\t\t\t{$category->title}: {$count}/{$filter->count}\n";
            foreach ($filter->requirements as $requirement) {
                $result .= "\t\t\t\t\t\t\t\t{$requirement->property->title} {$requirement->type} {$requirement->value} {$requirement->dimension->title}\n";
            }
        }
        $result .= \Yii::t('app', 'Comment') . ": " . $this->comment . "\n";
        $result .= \Yii::t('app', 'Attachments') . ":\n";
        foreach ($this->attachments as $attach) {
            $result .= \yii\helpers\Url::to($attach->url, true) . "\n";
        }
        return $result;
    }

    public function issetCoworkers($priority)
    {
        $coworker = [];
        foreach ($this->filters as $filter) {
            $coworker = array_merge($coworker, $filter->findCoworkers($priority));
        }
        return $coworker;
    }

    public function getDetails()
    {
        return [
            "id" => $this->id,
            "building" => $this->building,
            "status" => $this->statusTitle,
            "type" => $this->typeName,
            "date" => $this->date,
            "comment" => $this->comment,
            "coworkers" => $this->coworkers,
            "filtered" => $this->issetCoworkers($this->priority > -1 ? $this->priority : 0),
            "filters" => $this->filters,
            "attachments" => $this->attachments,
        ];
    }

    public function getSuitableCoworkers()
    {
        $coworkers = [];
        foreach ($this->filters as $filter) {
            $coworkers = array_merge($coworkers, $filter->findCoworkers($this->priority_level));
        }
        return $coworkers;
    }

    public function assignCoworker(Coworker $coworker)
    {
//        \Yii::error( $this->checkSuccessfully() );
        if (!$this->checkSuccessfully()) {
            $this->link('coworkers', $coworker);
        }
        if ($this->checkSuccessfully()) {
// @TODO: This invalid query!!! Must select order => $order_id && coworker_id => [$order->coworkers && not $order->created_by]
            $messages = TelegramMessage::findAll(['and', ['order_id' => $this->id], ['<>', 'coworker_id' => array_merge(\yii\helpers\ArrayHelper::map($this->coworkers, 'id', 'id'), [$coworker->id => $coworker->id])]]);
            foreach ($messages as $message) {
                \Yii::error($message->message_id);
                $message->deleteMessage();
            }
        } else {
            $messages = TelegramMessage::findAll(['order_id' => $this->id]);
            foreach ($messages as $message) {
                $message->editMessageText($this->generateTelegramText(\Yii::t('app', 'You have agreed to complete this order'). "#{$this->id}"));
            }
        }
//        $this->notify();
    }
}
