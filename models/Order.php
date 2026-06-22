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
use garmayev\max\MessageBuilder;

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
 * @property string $summary
 * @property int $is_payed
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
 * @property Hours[] $hours
 * @property bool $isPayed
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
        // Проверяем, не является ли это AJAX валидацией
        if (Yii::$app->request->isAjax && Yii::$app->request->getIsPost()) {
            // Пропускаем определенную логику для AJAX валидации
            $this->date = $this->date ?? time(); // Простое присвоение без форматирования
        } else {
            $this->date = $this->date ?? Yii::$app->formatter->asTimestamp($this->datetime);
        }

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
            [['building_id', 'mode'], 'required'],

            // Правила валидации дат с правильной обработкой
            [['start_datetime', 'finish_datetime'], 'required', 'when' => function ($model) {
                return $model->mode == self::MODE_LONG_FIXED || $model->mode == self::MODE_LONG_DAILY;
            }, 'whenClient' => 'function (attribute, value) {return $("#order-mode").val() === 1 || $("#order-mode").val() === 2}', 'message' => 'Выберите дату начала и окончания'],

            [['start_datetime'], 'required', 'when' => function ($model) {
                return $model->mode == self::MODE_SINGLE_FIXED;
            }, 'message' => 'Выберите дату начала'],

            // Убираем старое правило date и добавляем кастомные валидаторы
            [['start_datetime'], 'validateStartDate'],
            [['finish_datetime'], 'validateFinishDate'],
            [['start_datetime', 'finish_datetime'], 'validateDateRange'], // НОВЫЙ ВАЛИДАТОР

            [['start_datetime', 'finish_datetime'], 'date', 'format' => 'php:Y-m-d H:i:s'],

            [['status', 'building_id', 'date', 'type', 'created_by', 'created_at', 'priority_level', 'mode', 'is_payed'], 'integer'],
            [['building_id'], 'exist', 'skipOnError' => true, 'targetClass' => Building::class, 'targetAttribute' => ['building_id' => 'id']],
            [['priority_level'], 'default', 'value' => Coworker::PRIORITY_HIGH],
            [['mode'], 'in', 'range' => [self::MODE_SINGLE_FIXED, self::MODE_LONG_FIXED, self::MODE_LONG_DAILY]],
            [['mode'], 'default', 'value' => self::MODE_LONG_DAILY],
            [['status'], 'default', 'value' => self::STATUS_NEW],
            [['comment', 'title'], 'string'],
            [['summary'], 'string', 'max' => 11],
            [['summary'], 'default', 'value' => ''],
            [['title'], 'string', 'max' => 255],

            // Правила валидации для requirements
            [['requirements'], 'required', 'message' => Yii::t('app', 'You must specify at least one requirement'), 'whenClient' => 'function (attribute, value) {
                // Проверяем, есть ли хотя бы один заполненный элемент requirements
                var hasRequirements = false;
                
                // Если это обычная форма с полями
                if ($("[name*=\'requirements\']").length > 0) {
                    // Проверяем все поля requirements
                    $("[name*=\'requirements\']").each(function() {
                        var fieldName = $(this).attr("name");
                        if (fieldName.includes("[property_id]") || fieldName.includes("[category_id]")) {
                            if ($(this).val() && $(this).val().trim() !== "") {
                                hasRequirements = true;
                                return false; // break loop
                            }
                        }
                    });
                }
                
                // Если requirements передаются как JSON или массив
                if (!hasRequirements && value && value.trim() !== "") {
                    try {
                        var reqData = JSON.parse(value);
                        if (Array.isArray(reqData) && reqData.length > 0) {
                            // Проверяем, что есть хотя бы одно заполненное требование
                            for (var i = 0; i < reqData.length; i++) {
                                var req = reqData[i];
                                if (req && req.property_id && req.category_id && req.value) {
                                    hasRequirements = true;
                                    break;
                                }
                            }
                        }
                    } catch(e) {
                        // Если не JSON, проверяем как массив
                        if (Array.isArray(value) && value.length > 0) {
                            hasRequirements = true;
                        }
                    }
                }
                
                return !hasRequirements;
            }'
            ],
            [['requirements'], 'validateRequirements'],
            // Отключаем требование requirements для AJAX валидации
            [['requirements'], 'required',
                'when' => function ($model) {
                    // Только для реального сохранения, не для AJAX валидации
                    return !Yii::$app->request->isAjax;
                },
                'message' => Yii::t('app', 'You must specify at least one requirement')
            ],

            [['datetime', 'attachments'], 'safe'],
            [['created_at'], 'default', 'value' => time()],

            [['price'], 'required', 'when' => function($model) {
                return $model->mode !== self::MODE_LONG_DAILY;
            }, 'whenClient' => 'function (attribute, value, model) {
                return $("#order-mode").val() !== "'.self::MODE_LONG_DAILY.'";
            }', 'message' => Yii::t('app', 'Price field is required')],
            [['mode', 'price'], 'validatePrice'],

            [['files'], 'file', 'skipOnEmpty' => true, 'extensions' => ['jpg', 'jpeg', 'png', 'svg', 'bmp', 'doc', 'docx', 'pdf', 'xls', 'xlsx'], 'maxFiles' => 10],
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
            'title' => Yii::t('app', 'Title'),
            'typeName' => Yii::t('app', 'Order Type'),
            'comment' => Yii::t('app', 'Comment'),
            'attachments' => Yii::t('app', 'Attachments'),
            'mode' => Yii::t('app', 'Mode'),
            'price' => Yii::t('app', 'Price'),
            'summary' => Yii::t('app', 'Summary'),
            'priority_level' => Yii::t('app', 'Priority'),
            'start_datetime' => Yii::t('app', 'Start Date'),
            'finish_datetime' => Yii::t('app', 'End Date'),
            'isPayed' => Yii::t('app', 'Is payed'),
            'requirements' => \Yii::t('app', 'Requirements')
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
            'status',
            'statusName' => function (Order $model) {
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
            'mode',
            'price' => function (Order $model) {
                if ($model->mode == Order::MODE_LONG_DAILY) {
                    $result = [];
                    foreach ($model->coworkers as $coworker) {
                        $hours = $coworker->getHoursByOrder($model->id);
                        $debit = $credit = 0;
//                        \Yii::error(count($hours));
                        foreach ($hours as $hour) {
                            $debit += $hour->debit;
                            $credit += $hour->credit;
                        }
                        $result[$coworker->id] = ['debit' => $debit, 'credit' => $credit, 'total' => $debit + $credit];
                    }
                    return $result;
                }
                return $model->price;
            },
            'start_datetime',
            'finish_datetime',
            'is_payed',
            'title',
//            'coworkers' => function (Order $model) {
//                return $model->coworkers;
//            },
            'requirements' => function (Order $model) {
                return $model->requirements;
            },
//            'hours',
        ];
    }

    /**
     * Валидация цены в зависимости от режима заказа
     */
    public function validatePrice($attribute, $params)
    {
        // Если режим MODE_LONG_DAILY - цена может быть 0, валидация не требуется
        if (intval($this->mode) === intval(self::MODE_LONG_DAILY)) {
            $this->clearErrors('price');
            return;
        }

        // Для других режимов цена обязательна и должна быть > 0
        if (empty($this->price) || $this->price == 0 || $this->price === '') {
            $this->addError('price', Yii::t('app', 'You must specify a price greater than 0'));
            return;
        }

        // Проверяем, что цена - положительное число
        $price = (float) $this->price;
        if ($price <= 0) {
            $this->addError('price', Yii::t('app', 'The price must be a positive number'));
        }
    }

    /**
     * Валидация взаимосвязи дат начала и окончания
     */
    public function validateDateRange($attribute, $params)
    {
        // Проверяем оба поля одновременно
        $startDate = $this->start_datetime;
        $finishDate = $this->finish_datetime;

        // Проверяем только если оба поля заполнены
        if (empty($startDate) || empty($finishDate)) {
            return;
        }

        $startTimestamp = strtotime($startDate);
        $finishTimestamp = strtotime($finishDate);
        $today = strtotime(date('Y-m-d 00:00:00'));

        $errors = [];

        // Проверяем дату начала относительно сегодня
        if ($startTimestamp < $today) {
            $errors['start'] = Yii::t('app', 'The start date cannot be earlier than today');
        }

        // Проверяем дату окончания относительно сегодня
        if ($finishTimestamp < $today) {
            $errors['finish'] = Yii::t('app', 'The end date cannot be earlier than the start date');
        }

        // Проверяем, что дата окончания не раньше даты начала
        if ($finishTimestamp < $startTimestamp) {
            $errors['finish'] = Yii::t('app', 'The end date cannot be earlier than today');
        }

        // Добавляем ошибки на соответствующие поля
        foreach ($errors as $field => $error) {
            if ($field === 'start') {
                $this->addError('start_datetime', $error);
            } elseif ($field === 'finish') {
                $this->addError('finish_datetime', $error);
            }
        }
    }

    /**
     * Валидация даты начала
     */
    public function validateStartDate($attribute, $params)
    {
        // Проверяем только базовую валидацию, основную логику перенесли в validateDateRange
        if (!$this->hasErrors() && $this->$attribute) {
            $startDate = strtotime($this->$attribute);
            // Базовые проверки, если нужно
        }
    }

    /**
     * Валидация даты окончания
     */
    public function validateFinishDate($attribute, $params)
    {
        // Проверяем только базовую валидацию, основную логику перенесли в validateDateRange
        if (!$this->hasErrors() && $this->{$attribute}) {
            // Базовые проверки, если нужно
        }
    }

    /**
     * Валидация массива requirements
     */
    public function validateRequirements($attribute, $params)
    {
        if (!is_array($this->$attribute) || empty($this->$attribute)) {
            $this->addError($attribute, Yii::t('app', 'The requirements must be specified as an array'));
            return;
        }

        if (count($this->{$attribute}) === 0) {
            $this->addError($attribute, Yii::t('app', 'You must specify at least one valid requirement'));
        }
    }

    public function getIsPayed()
    {
        if ($this->mode !== Order::MODE_LONG_DAILY) {
            return $this->is_payed === 1;
        }
        $result = true;
        foreach ($this->hours as $hour) {
            if ($hour->is_payed === 0) {
                $result = false;
                break;
            }
        }
        return $result;
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
            // Обработка URL вложений
            if (!empty($attachments)) {
//                \Yii::error("process Url Attachments");
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
//        \Yii::error($this->files);
        foreach ($this->files as $file) {
            if (!$file instanceof \yii\web\UploadedFile) {
                continue;
            }

            $attachment = new Attachment([
                'file' => $file,
                'target_class' => self::class,
            ]);

            if ($attachment->upload() && $attachment->save()) {
//                Yii::error('Attachment saved');
                $attachments[] = $attachment;
            } else {
                Yii::error('Failed to upload file: ' . $file->name);
                Yii::error($attachment->errors);
            }
        }

//        \Yii::error($attachments);

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
        return $this->hasMany(Coworker::class, ['id' => 'user_id'])
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
        if (\Yii::$app->request->isAjax) {
            return;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->save(false);
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
        // Основной запрос для поиска подходящих пользователей
        return Coworker::find()
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
                ->where('up.user_id = coworker.id')
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

    public function canAssignCoworker(User $coworker): bool
    {
        // Если сотрудник уже назначен на этот заказ
        $coworkersIds = ArrayHelper::getColumn($this->coworkers, 'id');
        if (in_array($coworker->id, $coworkersIds)) {
            return false;
        }

        // Получаем даты текущего заказа
        $startDate = $this->start_datetime ? strtotime($this->start_datetime) : null;
        $finishDate = $this->finish_datetime ? strtotime($this->finish_datetime) : null;

        // Если у текущего заказа нет дат начала/окончания - всегда можно назначить
        if (!$startDate && !$finishDate) {
            \Yii::error("Free order");
            return true;
        }

        // Для заказов без даты окончания используем дату начала как окончание
        if (!$finishDate) {
            
            $finishDate = $startDate;
        }

        // Получаем все заказы сотрудника (кроме текущего)
        $existingOrders = Order::find()
            ->alias('o')
            ->innerJoin('order_user ou', 'ou.order_id = o.id')
            ->where(['ou.user_id' => $coworker->id])
            ->andWhere(['<>', 'o.id', $this->id])
            ->andWhere(['<>', 'o.status', Order::STATUS_COMPLETE]) // Исключаем завершенные заказы
            ->all();

        foreach ($existingOrders as $existingOrder) {
            // Получаем даты существующего заказа
            $existingStart = $existingOrder->start_datetime ? strtotime($existingOrder->start_datetime) : null;
            $existingFinish = $existingOrder->finish_datetime ? strtotime($existingOrder->finish_datetime) : null;

            // Если у существующего заказа нет дат - пропускаем
            if (!$existingStart && !$existingFinish) {
                continue;
            }

            // Для заказов без даты окончания используем дату начала как окончание
            if (!$existingFinish) {
                $existingFinish = $existingStart;
            }

            // Проверяем пересечение временных интервалов
            $intersects = $this->dateRangesIntersect(
                $startDate, $finishDate,
                $existingStart, $existingFinish
            );
            if ($intersects) {
                return false; // Найдено пересечение - нельзя назначить
            }
        }

        return true;
    }

    /**
     * Проверяет пересечение двух временных интервалов
     *
     * @param int|null $start1 Начало первого интервала (timestamp)
     * @param int|null $end1 Окончание первого интервала (timestamp)
     * @param int|null $start2 Начало второго интервала (timestamp)
     * @param int|null $end2 Окончание второго интервала (timestamp)
     * @return bool true если интервалы пересекаются
     */
    private function dateRangesIntersect(?int $start1, ?int $end1, ?int $start2, ?int $end2): bool
    {
        // Если какой-то из интервалов неопределен - считаем что пересечения нет
        if (!$start1 || !$end1 || !$start2 || !$end2) {
            return false;
        }

        // Проверяем пересечение интервалов (включая граничные случаи)
        return ($start1 <= $end2 && $end1 >= $start2);
    }

    /**
     * Assigns a coworker to the order
     *
     * @param User $coworker The coworker to assign
     * @throws Exception
     */
    public function assignCoworker(User $coworker): bool
    {
        if (!$this->canAssignCoworker($coworker)) {
            // Yii::error("Cannot assign coworker {$coworker->id} to order {$this->id} - date conflict or already assigned");
            return false;
        }

        $coworkersIds = ArrayHelper::getColumn($this->coworkers, 'id');
        if (!in_array($coworker->id, $coworkersIds)) {
            $this->link('coworkers', $coworker);
            return $this->save();
        }
        return false;
    }

    /**
     * Рассчитывает стоимость заказа в зависимости от режима
     *
     * @return float
     */
    public function calculateTotalPrice(): float
    {
        switch ($this->mode) {
            case self::MODE_SINGLE_FIXED:
                return (float)$this->price;

            case self::MODE_LONG_FIXED:
                return (float)$this->price;

            case self::MODE_LONG_DAILY:
                $days = $this->getWorkingDaysCount();
                return (float)$this->price * $days;

            default:
                return 0;
        }
    }

    /**
     * Проверяет, требуется ли ежедневный отчет для заказа
     *
     * @return bool
     */
    public function requiresDailyReports(): bool
    {
        return $this->mode === self::MODE_LONG_DAILY;
    }

    /**
     * Получает все отчеты по заказу, сгруппированные по дате
     *
     * @return array
     */
    public function getReportsByDate(): array
    {
        $reports = $this->getReports()->with('attachments')->all();
        $grouped = [];

        foreach ($reports as $report) {
            $date = date('Y-m-d', $report->created_at);
            if (!isset($grouped[$date])) {
                $grouped[$date] = [];
            }
            $grouped[$date][] = $report;
        }

        return $grouped;
    }

    /**
     * Проверяет, заполнены ли все необходимые отчеты
     *
     * @return bool
     */
    public function hasAllRequiredReports(): bool
    {
        if ($this->mode !== self::MODE_LONG_DAILY) {
            return true; // Для не-ежедневных режимов отчеты не обязательны
        }

        $requiredDays = $this->getWorkingDaysCount();
        $actualReports = count($this->reports);

        return $actualReports >= $requiredDays;
    }

    /**
     * Получает количество рабочих дней в периоде
     *
     * @return int
     */
    private function getWorkingDaysCount(): int
    {
        if (!$this->start_datetime || !$this->finish_datetime) {
            return 1;
        }

        $start = new \DateTime($this->start_datetime);
        $end = new \DateTime($this->finish_datetime);
        $end->modify('+1 day'); // включительно

        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($start, $interval, $end);

        $workingDays = 0;
        foreach ($period as $date) {
            $workingDays++;
        }

        return $workingDays;
    }

    public function getAttachImages()
    {
        $attachments = $this->getAttachments()->all();
        $images = [];
        foreach ($attachments as $attachment) {
            if ($attachment->isImage()) {
                $images[] = $attachment;
            }
        }
        return $images;
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
            $title = !empty($this->title) ? "({$this->title})" : "";
            $formattedMessage = '<b>' . \Yii::t('app', 'Order #{id}', ['id' => $this->id]) . " {$title}</b>\n" . $messageText;
//            \Yii::error($formattedMessage);
            $coworkerKeyboard = json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => Yii::t('app', 'Accept'), 'callback_data' => "/accept order_id={$this->id}"],
                        ['text' => Yii::t('app', 'Decline'), 'callback_data' => "/decline order_id={$this->id}"]
                    ]
                ]
            ]);

            // 1. Обновление существующих сообщений
            foreach ($this->telegramMessages as $message) {
                $message->editText($formattedMessage, $coworkerKeyboard);
            }

            // 2. Подготовка данных для массовой проверки
            $assignedCoworkerIds = ArrayHelper::getColumn($this->coworkers, 'id');
            $existingChatIds = ArrayHelper::getColumn($this->telegramMessages, 'chat_id');
//            \Yii::error(count($this->suitableCoworkers));
            // 3. Отправка уведомлений подходящим сотрудникам
            foreach ($this->suitableCoworkers as $coworker) {
/*                if ($coworker->status !== User::STATUS_ACTIVE ||
                    in_array($coworker->id, $assignedCoworkerIds)) {
                    continue;
                } */
//                \Yii::error($coworker->attributes);
//                \Yii::error($this->canAssignCoworker($coworker));
                if (!$this->canAssignCoworker($coworker)) {
                    continue;
                }

                $profile = $coworker->profile;
                if (!$profile) continue;
                    $coworkerKeyboard = [MessageBuilder::row([MessageBuilder::callbackButton("Принять заказ", "command_accept id={$this->id}")]), MessageBuilder::row([MessageBuilder::callbackButton("Отказаться", "command_reject id={$this->id}")])];
                // Telegram сообщения
//                if ($profile->chat_id || $profile->max_id) {
//                    \Yii::error($profile->chat_id ?? $profile->max_id);
                    $message = TelegramMessage::find()->where(['chat_id' => $profile->chat_id ?? $profile->max_id])->andWhere(['order_id' => $this->id])->one();
//                    if ((!in_array($profile->chat_id, $existingChatIds) || !in_array($profile->max_id, $existingChatIds)) && empty($message)) {
//                        \Yii::error($profile->chat_id ?? $profile->max_id);

                        $telegramMsg = new TelegramMessage([
                            'chat_id' => $profile->chat_id ?? $profile->max_id,
                            'order_id' => $this->id,
                            'text' => $formattedMessage,
                            'reply_markup' => $coworkerKeyboard,
                            'created_at' => time(),
                            'updated_at' => time(),
                        ]);
                        $telegramMsg->send();
//                    }
//                }
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
            if (!$this->isOwnerNotified() && $this->owner->profile) {
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
            \Yii::error('Error in sendAndUpdateTelegramNotifications: ' . $e->getMessage());
            \Yii::error($e);
        }
        return [];
    }
}