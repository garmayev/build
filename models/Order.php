<?php

namespace app\models;

use app\components\Helper;
use app\models\telegram\TelegramMessage;
use ExpoSDK\Expo;
use ExpoSDK\ExpoMessage;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;

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
 * @property int $priority_level
 * @property int $created_at
 *
 * @property string $statusTitle
 * @property array $statusList
 * @property array $details
 *
 * @property Building $building
 * @property User[] $coworkers
 * @property Filter[] $filters
 * @property Technique[] $techniques
 * @property Attachment[] $attachments
 * @property Requirement[] $requirements
 * @property TelegramMessage[] $telegramMessages
 * @property User $owner
 *
 * @property int $requiredCoworkers
 * @property int $issetCoworkers
 * @property User[] $suitableCoworkers
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * @var string Temporary storage for datetime input
     */
    public $datetime;

    /**
     * @var array Array of uploaded files
     */
    public $files = [];

    /**
     * Order status constants
     */
    const STATUS_NEW = 0;
    const STATUS_PROCESS = 1;
    const STATUS_BUILD = 2;
    const STATUS_COMPLETE = 3;

    /**
     * Order type constants
     */
    const TYPE_COWORKER = 1;
    const TYPE_MATERIAL = 2;
    const TYPE_TECHNIQUE = 3;

    /**
     * Defines the behaviors for the model
     * Adds automatic timestamp and blame handling
     *
     * @return array Array of behaviors
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => false,
            ],
        ];
    }

    /**
     * Returns the table name for this model
     *
     * @return string The table name
     */
    public static function tableName(): string
    {
        return 'order';
    }

    /**
     * Handles operations before deleting the model
     * Deletes all related filters and telegram messages within a transaction
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
            foreach ($this->telegramMessages as $message) {
                $message->remove();
            }
            $transaction->commit();
            return parent::beforeDelete();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error deleting order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handles operations before validating the model
     * Sets date from datetime and processes file attachments
     *
     * @return bool Whether validation should continue
     */
    public function beforeValidate()
    {
        $this->date = $this->date ?? Yii::$app->formatter->asTimestamp($this->datetime);
        if (!empty($this->files)) {
            $this->attachments = $this->files;
        }
        return parent::beforeValidate();
    }

    public function afterFind()
    {
        $this->datetime = \Yii::$app->formatter->asDate($this->date, 'php:d.m.Y');
        parent::afterFind();
    }

    /**
     * Defines validation rules for model attributes
     *
     * @return array Array of validation rules
     */
    public function rules(): array
    {
        return [
            [['status', 'building_id', 'date', 'type', 'created_by', 'created_at', 'priority_level'], 'integer'],
            [['building_id'], 'exist', 'skipOnError' => true, 'targetClass' => Building::class, 'targetAttribute' => ['building_id' => 'id']],
            [['priority_level'], 'default', 'value' => User::PRIORITY_HIGH],
            [['comment'], 'string'],
            [['datetime', 'attachments', 'requirements'], 'safe'],
            [['created_at'], 'default', 'value' => time()],
        ];
    }

    /**
     * Defines attribute labels for the model
     *
     * @return array Array of attribute labels
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
     * Defines which fields should be exposed in API responses
     *
     * @return array Array of fields and their formatters
     */
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
                return Attachment::find()
                    ->where(['target_class' => Order::class])
                    ->andWhere(['target_id' => $model->id])
                    ->all();
            },
            'coworkers' => function (Order $model) {
                return $model->coworkers;
            },
            'requirements' => function (Order $model) {
                return $model->requirements;
            },
            'hours',
        ];
    }

    /**
     * Gets the related Building model
     *
     * @return ActiveQuery Query for the related Building
     */
    public function getBuilding(): ActiveQuery
    {
        return $this->hasOne(Building::class, ['id' => 'building_id']);
    }

    /**
     * Gets related Attachment models
     *
     * @return ActiveQuery Query for related Attachments
     */
    public function getAttachments()
    {
        return $this->hasMany(Attachment::class, ['target_id' => 'id'])
            ->andWhere(['target_class' => Order::class]);
    }

    /**
     * Sets attachments for the order within a transaction
     * Handles both file uploads and URL attachments
     *
     * @param array $data Array of attachment data
     * @throws \Exception if setting attachments fails
     */
    public function setAttachments($data)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->save(false);
            if (!empty($this->files)) {
                foreach ($this->files as $item) {
                    $attach = new Attachment();
                    $attach->file = $item;
                    $attach->target_class = Order::class;
                    if ($attach->upload() && $attach->save()) {
                        $this->link('attachments', $attach, ['target_class' => Order::class]);
                    }
                }
            } else if ($data) {
                foreach ($this->attachments as $attachment) {
                    $this->unlink('attachments', $attachment, true);
                }
                foreach ($data as $item) {
                    $attach = new Attachment();
                    $attach->url = $item;
                    $attach->target_class = Order::class;
                    if ($attach->save()) {
                        $this->link('attachments', $attach, ['target_class' => Order::class]);
                    }
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error setting attachments: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gets related Hours models
     *
     * @return ActiveQuery Query for related Hours
     */
    public function getHours()
    {
        return $this->hasMany(Hours::class, ['order_id' => 'id']);
    }

    /**
     * Gets related Coworker models through order_coworker table
     *
     * @return ActiveQuery Query for related Coworkers
     * @throws InvalidConfigException if the configuration is invalid
     */
    public function getCoworkers(): ActiveQuery
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('order_user', ['order_id' => 'id']);
    }

    /**
     * Gets the status title for current status
     *
     * @return string Localized status title
     */
    public function getStatusTitle()
    {
        return $this->getStatusList()[$this->status] ?? Yii::t('app', 'Unknown Status');
    }

    /**
     * Gets list of all possible statuses
     *
     * @return array Array of status titles indexed by status codes
     */
    public function getStatusList(): array
    {
        return [
            self::STATUS_NEW => Yii::t('app', 'New Order'),
            self::STATUS_PROCESS => Yii::t('app', 'Order in process'),
            self::STATUS_BUILD => Yii::t('app', 'Order building'),
            self::STATUS_COMPLETE => Yii::t('app', 'Order completed'),
        ];
    }

    /**
     * Gets the type name for current or specified type
     *
     * @param int|null $type Optional type code
     * @return string Localized type name
     */
    public function getTypeName($type = null)
    {
        $list = [
            self::TYPE_COWORKER => Yii::t('app', 'Coworker'),
            self::TYPE_MATERIAL => Yii::t('app', 'Material'),
            self::TYPE_TECHNIQUE => Yii::t('app', 'Technique'),
        ];
        return $list[$type ?? $this->type] ?? Yii::t('app', 'Unknown Type');
    }

    /**
     * Gets related Notification models
     *
     * @return ActiveQuery Query for related Notifications
     */
    public function getNotifications()
    {
        return $this->hasMany(Notification::class, ['order_id' => 'id']);
    }

    public function getRequirements(): ActiveQuery
    {
        return $this->hasMany(Requirement::class, ['order_id' => 'id']);
    }

    public function setRequirements($data)
    {
        $transaction = Yii::$app->db->beginTransaction();
        $this->save(false);
        try {
            foreach ($this->requirements as $requirement) {
                $this->unlink('requirements', $requirement, true);
            }
            foreach ($data as $item) {
                $requirement = new Requirement($item);
                if ($requirement->save()) {
//                    \Yii::error($requirement->attributes);
                    $this->link('requirements', $requirement);
                } else {
                    \Yii::error($requirement->errors);
                }
            }
            $transaction->commit();
        } catch (\Exception $exception) {
            Yii::error('Error setting requirements: ' . $exception->getMessage());
            $transaction->rollBack();
            throw $exception;
        }
    }

    public function getOderUsers()
    {
        return $this->hasMany(OrderUser::class, ['order_id' => 'id']);
    }

    /**
     * Gets related TelegramMessage models
     *
     * @return ActiveQuery Query for related TelegramMessages
     */
    public function getTelegramMessages(): ActiveQuery
    {
        return $this->hasMany(TelegramMessage::class, ['order_id' => 'id']);
    }

    /**
     * Calculates total required coworkers based on filters
     *
     * @return int Total number of required coworkers
     */
    public function getRequiredCoworkers()
    {
        /**
         * @var Requirement $requirement
         */
        $total = 0;
        foreach ($this->requirements as $requirement) {
            $total += $requirement->count;
        }
        return $total;
    }

    /**
     * Gets count of currently assigned coworkers
     *
     * @return int Number of assigned coworkers
     */
    public function getIssetCoworkers()
    {
        return count($this->coworkers);
    }

    /**
     * Checks if order has all required coworkers assigned
     *
     * @return bool True if all required coworkers are assigned
     */
    public function isFull(): bool
    {
        return $this->issetCoworkers === $this->requiredCoworkers;
    }

    /**
     * Gets array of suitable coworkers based on filters and priority level
     *
     * @return array Array of suitable Coworker models
     */
    public function getSuitableCoworkers(): array
    {
        $requirementSubQuery = Requirement::find()
            ->select(['property_id', 'dimension_id', 'category_id', 'type', 'value'])
            ->where(['order_id' => $this->id]);

        // Основной запрос для поиска подходящих пользователей
        return User::find()
            ->where(['and', ['priority_level' => $this->priority_level], ['referrer_id' => $this->owner->id]])
            ->andWhere(['exists', (new \yii\db\Query())
                ->select('*')
                ->from(['r' => $requirementSubQuery])
                ->leftJoin('user_property up', [
                    'and',
                    'up.property_id = r.property_id',
                    'up.dimension_id = r.dimension_id',
                    'up.category_id = r.category_id'
                ])
                ->where('up.user_id = user.id')
                ->andWhere([
                    'or',
                    ['and', ['r.type' => 'less'], ['<=', 'up.value', new \yii\db\Expression('r.value')]],
                    ['and', ['r.type' => 'more'], ['>=', 'up.value', new \yii\db\Expression('r.value')]],
                    ['and', ['r.type' => 'equal'], ['=', 'up.value', new \yii\db\Expression('r.value')]],
                    ['and', ['r.type' => 'not-equal'], ['!=', 'up.value', new \yii\db\Expression('r.value')]]
                ])
            ])->all();
    }

    /**
     * @return ActiveQuery
     */
    public function getOwner(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Assigns a coworker to the order
     *
     * @param User $coworker The coworker to assign
     * @throws Exception
     */
    public function assignCoworker($coworker)
    {
        $coworkersIds = \yii\helpers\ArrayHelper::getColumn($this->coworkers, 'id');
        if (!in_array($coworker->id, $coworkersIds)) {
            $this->link('coworkers', $coworker);
            return $this->save();
        }
        return false;
    }

    /**
     * Assigns a coworker to the order
     *
     * @param User $coworker The coworker to assign
     * @return bool
     * @throws Exception
     */
    public function revokeCoworker(User $coworker)
    {
        $coworkersIds = \yii\helpers\ArrayHelper::getColumn($this->coworkers, 'id');
        if (in_array($coworker->id, $coworkersIds)) {
            $this->unlink('coworkers', $coworker, true);
            return $this->save();
        }
        return false;
    }

    /**
     * Sends and updates Telegram notifications for the order
     * This method handles both initial sending and updating of notifications
     *
     * @return array Results of notification operations
     */
    public function sendAndUpdateTelegramNotifications()
    {
        try {
            // Prepare the message
            $message = Helper::generateTelegramMessage($this->id);

            // Prepare keyboard markup
            $keyboard = [
                [
                    ['text' => Yii::t('app', 'Accept'), 'callback_data' => "/accept order_id={$this->id}"],
                    ['text' => Yii::t('app', 'Decline'), 'callback_data' => "/decline order_id={$this->id}"]
                ]
            ];

            foreach ($this->suitableCoworkers as $coworker) {
//                \Yii::error($coworker->id);
                if ($coworker->status === User::STATUS_ACTIVE) {
                    if (!in_array($coworker->id, \yii\helpers\ArrayHelper::getColumn($this->coworkers, 'id'))) {
                        if (isset($coworker->profile->chat_id)) {
                            $telegramMessages = $this->telegramMessages;
                            if (count($telegramMessages)) {
                                foreach ($telegramMessages as $telegramMessage) {
                                    $telegramMessage->editMessageText('<b>'.\Yii::t('app', 'Order #{id}', ['id' => $this->id])."</b>\n".$message, json_encode(['inline_keyboard' => $keyboard]));
                                }
                            } else {
                                if ($coworker->profile->chat_id) {
                                    $telegramMsg = new \app\models\telegram\TelegramMessage([
                                        'chat_id' => $coworker->profile->chat_id,
                                        'order_id' => $this->id,
                                        'created_at' => time(),
                                        'updated_at' => time(),
                                        'text' => \Yii::t('app', 'Order #{id}', ['id' => $this->id])."\n".$message,
                                        'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
                                    ]);
                                    $telegramMsg->send();
                                    // Helper::notify($coworker->profile->chat_id, $this->id, null, $keyboard);
                                    // $notificationService->sendTelegramMessage($coworker->profile->chat_id, "<b>" . \Yii::t("app", "Order #{id}", ["id" => $this->id]) . "</b>\n" . $message, $keyboard, $this->id);
                                }
                            }
                        } else if (isset($coworker->profile->device_id)) {
//                            \Yii::error($coworker->id);
                            $expoMessage = (new ExpoMessage())
                                ->setTitle(\Yii::t('app', 'New Order').' #'.$this->id)
                                ->setBody(Helper::orderDetailsPlain($this))
                                ->setTo($coworker->profile->device_id)
                                ->setData(['url' => 'build://amgcompany.ru/--/order/'.$this->id, 'id' => $this->id])
                                ->setChannelId('new-order')
                                ->setCategoryId('new-order')
                                ->playSound();
                            $expo = new Expo();
                            $ticket = $expo->send($expoMessage)->push();
                            \Yii::error($ticket->getData());
                        }
                    }
                }
            }
            if (isset($this->owner->profile->chat_id)) {
                Helper::notify($this->owner->profile->chat_id, $this->id);
                $notificationService->sendTelegramMessage($this->owner->profile->chat_id, "<b>" . \Yii::t("app", "Order #{id}", ["id" => $this->id]) . "</b>\n" . $message, null, $this->id);
            }
        } catch (\Exception $e) {
            Yii::error('Error in sendAndUpdateTelegramNotifications: ' . $e->getMessage());
            $results['errors'][] = $e->getMessage();
            return $results;
        }
    }
}