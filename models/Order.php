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
 * @property int $notify_date
 * @property int $priority_level
 * @property int $created_at
 * @property int $mode
 * @property double $price
 *
 * @property string $statusTitle
 * @property array $statusList
 * @property array $modes
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
 * @property int $requiredCoworkers
 * @property int $issetCoworkers
 * @property User[] $suitableCoworkers
 * @property Report[] $reports
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
     * Статусы заказа
     *
     * STATUS_NEW - Новый заказ
     * STATUS_PROCESS - В процессе
     * STATUS_BUILD - Собран
     * STATUS_COMPLETE - Готов
     */
    const STATUS_NEW = 0;
    const STATUS_PROCESS = 1;
    const STATUS_BUILD = 2;
    const STATUS_COMPLETE = 3;

    /**
     * Типы заказа
     *
     * TYPE_COWORKER - Заказ сотрудника
     * TYPE_MATERIAL - Заказ материалов
     * TYPE_TECHNIQUE - Заказ техники
     */
    const TYPE_COWORKER = 1;
    const TYPE_MATERIAL = 2;
    const TYPE_TECHNIQUE = 3;

    /**
     * Режимы заказа
     *
     * MODE_SIMPLE
     * MODE_LONG
     */
    const MODE_SINGLE_FIXED = 0;
    const MODE_LONG_FIXED = 1;
    const MODE_LONG_DAILY = 2;

    /**
     * Длительность заказа
     *
     * DURATION_NONE
     * DURATION_FREE
     */
    const DURATION_NONE = 0;
    const DURATION_FREE = 1;

    /**
     * Поведение модели
     *
     * @return array Array of behaviors
     */
    public function behaviors(): array
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
     * Имя таблицы
     *
     * @return string The table name
     */
    public static function tableName(): string
    {
        return 'order';
    }

    /**
     * Перед удалением
     *
     * @return bool Whether the deletion should continue
     * @throws \Exception if deletion fails
     */
    public function beforeDelete(): bool
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
     * Перед проверкой модели
     *
     * @return bool Whether validation should continue
     */
    public function beforeValidate(): bool
    {
        $this->date = $this->date ?? Yii::$app->formatter->asTimestamp($this->datetime);
        return parent::beforeValidate();
    }

    /**
     * После поиска в БД
     *
     * @return void
     * @throws InvalidConfigException
     */
    public function afterFind()
    {
        $this->datetime = \Yii::$app->formatter->asDate($this->date, 'php:d.m.Y');
        parent::afterFind();
    }

    /**
     * Правила валидации модели
     *
     * @return array Array of validation rules
     */
    public function rules(): array
    {
        return [
            [['date', 'building_id', 'mode'], 'required'],
            [['status', 'building_id', 'date', 'type', 'created_by', 'created_at', 'priority_level', 'mode'], 'integer'],
            [['building_id'], 'exist', 'skipOnError' => true, 'targetClass' => Building::class, 'targetAttribute' => ['building_id' => 'id']],
            [['priority_level'], 'default', 'value' => User::PRIORITY_HIGH],
            [['mode'], 'in', 'range' => [self::MODE_SINGLE_FIXED, self::MODE_LONG_FIXED, self::MODE_LONG_DAILY]],
            [['mode'], 'default', 'value' => self::MODE_LONG_DAILY],
            [['status'], 'default', 'value' => self::STATUS_NEW],
            [['comment'], 'string'],
            [['datetime', 'attachments', 'requirements'], 'safe'],
            [['created_at'], 'default', 'value' => time()],
            [['price'], 'safe'],
            [['price'], 'filter', 'filter' => function($value) {
                return str_replace(' ', '', $value);
            }],
            [['price'], 'match', 'pattern' => '/^[0-9]{1,12}(\.[0-9]{0,2})?$/'],
            [['files'], 'file', 'skipOnEmpty' => true, 'extensions' => ['jpg','jpeg','png','svg','bmp'], 'maxFiles' => 10],
        ];
    }

    /**
     * Определяем метки атрибутов для модели
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
            'mode' => Yii::t('app', 'Mode'),
            'price' => Yii::t('app', 'Price')
        ];
    }

    /**
     * Определяем какие поля должны отображаться в ответах API
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
     * Получение объекта заказа
     *
     * @return ActiveQuery Query for the related Building
     */
    public function getBuilding(): ActiveQuery
    {
        return $this->hasOne(Building::class, ['id' => 'building_id']);
    }

    public function getModes()
    {
        return [
            Order::MODE_SINGLE_FIXED => \Yii::t('app', 'mode_single_fixed'),
            Order::MODE_LONG_FIXED => \Yii::t('app', 'mode_long_fixed'),
            Order::MODE_LONG_DAILY => \Yii::t('app', 'mode_long_daily')
        ];
    }

    /**
     * Получение вложений
     *
     * @return ActiveQuery Query for related Attachments
     */
    public function getAttachments()
    {
        return $this->hasMany(Attachment::class, ['target_id' => 'id'])
            ->andWhere(['target_class' => Order::class]);
    }

    /**
     * Установка вложений
     *
     * @param array $data Array of attachment data
     * @throws \Exception if setting attachments fails
     */
    public function setAttachments($data): bool
    {
        // Валидация входных данных
        if (empty($data) && empty($this->files)) {
            return true;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $attachments = [];
            // Обработка загруженных файлов
            if (!empty($this->files)) {
                \Yii::error("process Uploaded Files");
                $attachments = $this->processUploadedFiles();
            }
            \Yii::error($attachments);
            // Обработка URL вложений
            if (!empty($attachments)) {
                \Yii::error("process Url Attachments");
                $this->processUrlAttachments($attachments);
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error setting attachments: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getReports()
    {
        return $this->hasMany(Report::class, ['order_id' => 'id']);
    }

    /**
     * Обработка загруженных файлов
     *
     * @throws \Exception
     */
    private function processUploadedFiles(): array
    {
        $attachments = [];
        \Yii::error($this->files);
        foreach ($this->files as $file) {
            if (!$file instanceof \yii\web\UploadedFile) {
                continue;
            }
            
            $attachment = new Attachment([
                'file' => $file,
                'target_class' => self::class,
            ]);
            
            if ($attachment->upload() && $attachment->save()) {
                Yii::error('Attachment saved');
                $attachments[] = $attachment;
            } else {
                Yii::error('Failed to upload file: ' . $file->name);
                Yii::error($attachment->errors);
            }
        }

        \Yii::error($attachments);

        // Массовое связывание
        if (!empty($attachments)) {
            $this->linkMultiple('attachments', $attachments, ['target_class' => Order::class]);
        }
        return $attachments;
    }

    /**
     * Обработка URL вложений
     *
     * @param array $data
     * @throws \Exception
     */
    private function processUrlAttachments(array $data)
    {
        // Получаем существующие вложения одним запросом
        $existingAttachments = $this->getAttachments()->all();
        
        // Удаляем существующие вложения одним запросом
        if (!empty($existingAttachments)) {
            $attachmentIds = ArrayHelper::getColumn($existingAttachments, 'id');
            Attachment::deleteAll(['id' => $attachmentIds]);
        }
        
        // Подготавливаем данные для массовой вставки
        $attachments = [];
        foreach (ArrayHelper::getColumn($data, 'url') as $link) {
            if (empty($link)) continue;

            $attachments[] = [
                'url' => "$link",
                'target_class' => self::class,
                'target_id' => $this->id,
            ];
        }
        
        // Массовая вставка
        if (!empty($attachments)) {
            Yii::$app->db->createCommand()
                ->batchInsert("attachment", ["url", "target_class", "target_id"], $attachments)
                ->execute();
        }
    }

    /**
     * Массовое связывание моделей
     *
     * @param string $relationName
     * @param array $models
     * @param array $extraColumns
     * @throws \Exception
     */
    private function linkMultiple(string $relationName, array $models, array $extraColumns = [])
    {
        if (empty($models)) {
            return;
        }

        foreach ($models as $model) {
            $this->link($relationName, $model, $extraColumns);
        }
    }

    /**
     * Получение почасовки
     *
     * @return ActiveQuery Query for related Hours
     */
    public function getHours()
    {
        return $this->hasMany(Hours::class, ['order_id' => 'id']);
    }

    /**
     * Получение деталей заказа
     *
     * @return array Array of order details
     */
    public function getDetails(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->statusTitle,
            'type' => $this->typeName,
            'date' => $this->date,
            'comment' => $this->comment,
            'building' => $this->building,
            'attachments' => $this->attachments,
            'coworkers' => $this->coworkers,
            'requirements' => $this->requirements,
            'hours' => $this->hours,
            'requiredCoworkers' => $this->requiredCoworkers,
            'issetCoworkers' => $this->issetCoworkers,
            'isFull' => $this->isFull(),
            'owner' => $this->owner,
        ];
    }

    /**
     * Получение списка сотрудников, принявших заказ
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
     * Получение названия статуса для текущего заказа
     *
     * @return string Localized status title
     */
    public function getStatusTitle(): string
    {
        return $this->getStatusList()[$this->status] ?? Yii::t('app', 'Unknown Status');
    }

    /**
     * Получение списка статусов
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
     * Получение типа заказа
     *
     * @param int|null $type Optional type code
     * @return string Localized type name
     */
    public function getTypeName(int $type = null): string
    {
        $list = [
            self::TYPE_COWORKER => Yii::t('app', 'Coworker'),
            self::TYPE_MATERIAL => Yii::t('app', 'Material'),
            self::TYPE_TECHNIQUE => Yii::t('app', 'Technique'),
        ];
        return $list[$type ?? $this->type] ?? Yii::t('app', 'Unknown Type');
    }

    /**
     * Получение списка требований для заказа
     *
     * @return ActiveQuery
     */
    public function getRequirements(): ActiveQuery
    {
        return $this->hasMany(Requirement::class, ['order_id' => 'id']);
    }

    /**
     * Установка требований для заказа
     *
     * @param $data
     * @return void
     * @throws Exception
     * @throws StaleObjectException
     */
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
    public function getRequiredCoworkers(): int
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
    public function getIssetCoworkers(): int
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
        $userIds = \Yii::$app->authManager->getUserIdsByRole('employee');
        // Основной запрос для поиска подходящих пользователей
        return User::find()
            ->where(['and', ['priority_level' => $this->priority_level], ['referrer_id' => $this->owner->id]])
            ->andWhere(['id' => $userIds])
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
    public function assignCoworker(User $coworker): bool
    {
        $coworkersIds = \yii\helpers\ArrayHelper::getColumn($this->coworkers, 'id');
        if (!in_array($coworker->id, $coworkersIds)) {
            $this->link('coworkers', $coworker);
            return $this->save();
        }
        return false;
    }

    public function isOwnerNotified()
    {
        $profile = $this->owner->profile;
        if ($profile && $profile->chat_id) {
            $message = \app\models\telegram\TelegramMessage::find()->where(['chat_id' => $this->owner->profile->chat_id])->andWhere(['order_id' => $this->id])->one();
            return isset($message);
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
    public function revokeCoworker(User $coworker): bool
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
            // Генерация данных сообщения один раз
            $messageText = Helper::generateTelegramMessage($this->id);
            $formattedMessage = '<b>' . \Yii::t('app', 'Order #{id}', ['id' => $this->id]) . "</b>\n" . $messageText;
            $coworkerKeyboard = json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => Yii::t('app', 'Accept'), 'callback_data' => "/accept order_id={$this->id}"],
                        ['text' => Yii::t('app', 'Decline'), 'callback_data' => "/decline order_id={$this->id}"]
                    ]
                ]
            ]);
//            \Yii::error($formattedMessage);
            // 1. Обновление существующих сообщений
            foreach ($this->telegramMessages as $message) {
                $message->editMessageText($formattedMessage, $coworkerKeyboard);
            }

            // 2. Подготовка данных для массовой проверки
            $assignedCoworkerIds = ArrayHelper::getColumn($this->coworkers, 'id');
            $existingChatIds = ArrayHelper::getColumn($this->telegramMessages, 'chat_id');

            // 3. Отправка уведомлений подходящим сотрудникам
            foreach ($this->suitableCoworkers as $coworker) {
                if ($coworker->status !== User::STATUS_ACTIVE ||
                    in_array($coworker->id, $assignedCoworkerIds)) {
                    continue;
                }

                $profile = $coworker->profile;
                if (!$profile) continue;

                // Telegram сообщения
                if ($profile->chat_id) {
                    if (!in_array($profile->chat_id, $existingChatIds)) {
                        $telegramMsg = new TelegramMessage([
                            'chat_id' => $profile->chat_id,
                            'order_id' => $this->id,
                            'text' => $formattedMessage,
                            'reply_markup' => $coworkerKeyboard,
                            'created_at' => time(),
                            'updated_at' => time(),
                        ]);
                        $telegramMsg->send();
                    }
                }
                // Push-уведомления
/*                elseif ($profile->device_id) {
                    $expoMessage = (new ExpoMessage())
                        ->setTitle(\Yii::t('app', 'New Order') . ' #' . $this->id)
                        ->setBody(Helper::orderDetailsPlain($this))
                        ->setTo($profile->device_id)
                        ->setData(['url' => 'build://amgcompany.ru/--/order/' . $this->id, 'id' => $this->id])
                        ->setChannelId('new-order')
                        ->setCategoryId('new-order')
                        ->playSound();
                    (new Expo())->send($expoMessage)->push();
                } */
            }

            // 4. Уведомление владельца
            if (!$this->isOwnerNotified()) {
                $telegramMsg = new TelegramMessage([
                    'chat_id' => $this->owner->profile->chat_id,
                    'order_id' => $this->id,
                    'text' => "<b>" . \Yii::t("app", "Order #{id}", ["id" => $this->id]) . "</b>\n" . $messageText,
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            [['text' => \Yii::t('app', 'Set order to status process'), 'callback_data' => "/order_status_process order_id={$this->id}"]],
                        ]
                    ]),
                    'created_at' => time(),
                    'updated_at' => time(),
                ]);
                $telegramMsg->send();
            }

        } catch (\Exception $e) {
            Yii::error('Error in sendAndUpdateTelegramNotifications: ' . $e->getMessage());
        }
        return [];
    }
}