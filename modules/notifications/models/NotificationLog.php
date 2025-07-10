<?php

namespace app\modules\notifications\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * Лог отправки уведомлений
 *
 * @property int $id
 * @property int $notification_id ID уведомления
 * @property string $channel Канал отправки (telegram, fcm, apns)
 * @property string $recipient Получатель (chat_id, token и т.д.)
 * @property int $status Статус отправки
 * @property string|null $response Ответ сервиса
 * @property string|null $action_data Данные действия (если было взаимодействие)
 * @property int|null $action_status Статус обработки действия
 * @property int $created_at Время создания
 * @property int|null $updated_at Время обновления
 *
 * @property Notification $notification Связанное уведомление
 */
class NotificationLog extends ActiveRecord
{
    // Статусы отправки
    const STATUS_PENDING = 0;
    const STATUS_SENT = 1;
    const STATUS_DELIVERED = 2;
    const STATUS_FAILED = 3;
    const STATUS_READ = 4;

    // Статусы обработки действий
    const ACTION_PENDING = 0;
    const ACTION_SUCCESS = 1;
    const ACTION_FAILED = 2;
    const ACTION_CANCELLED = 3;

    public static function tableName()
    {
        return '{{%notification_logs}}';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

    public function rules()
    {
        return [
            [['notification_id', 'channel', 'recipient'], 'required'],
            [['notification_id', 'status', 'action_status'], 'integer'],
            ['status', 'default', 'value' => self::STATUS_PENDING],
            ['status', 'in', 'range' => array_keys(self::statusLabels())],
            ['action_status', 'in', 'range' => array_keys(self::actionStatusLabels())],
            [['channel', 'recipient'], 'string', 'max' => 255],
            [['response', 'action_data'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'notification_id' => 'Уведомление',
            'channel' => 'Канал',
            'recipient' => 'Получатель',
            'status' => 'Статус',
            'response' => 'Ответ сервиса',
            'action_data' => 'Данные действия',
            'action_status' => 'Статус действия',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    /**
     * Связь с уведомлением
     */
    public function getNotification()
    {
        return $this->hasOne(Notification::class, ['id' => 'notification_id']);
    }

    /**
     * Метки статусов
     */
    public static function statusLabels()
    {
        return [
            self::STATUS_PENDING => 'В ожидании',
            self::STATUS_SENT => 'Отправлено',
            self::STATUS_DELIVERED => 'Доставлено',
            self::STATUS_FAILED => 'Ошибка',
            self::STATUS_READ => 'Прочитано',
        ];
    }

    /**
     * Метки статусов действий
     */
    public static function actionStatusLabels()
    {
        return [
            self::ACTION_PENDING => 'Не обработано',
            self::ACTION_SUCCESS => 'Успешно',
            self::ACTION_FAILED => 'Ошибка',
            self::ACTION_CANCELLED => 'Отменено',
        ];
    }

    /**
     * Получить текстовое представление статуса
     */
    public function getStatusText()
    {
        $labels = self::statusLabels();
        return $labels[$this->status] ?? 'Неизвестно';
    }

    /**
     * Получить текстовое представление статуса действия
     */
    public function getActionStatusText()
    {
        $labels = self::actionStatusLabels();
        return $labels[$this->action_status] ?? 'Неизвестно';
    }

    /**
     * Получить данные действия в виде массива
     */
    public function getActionData()
    {
        if (!empty($this->action_data)) {
            try {
                return Json::decode($this->action_data);
            } catch (\Exception $e) {
                \Yii::error("Error decoding action data: " . $e->getMessage());
            }
        }
        return [];
    }

    /**
     * Установить данные действия
     */
    public function setActionData($data)
    {
        $this->action_data = Json::encode($data);
    }

    /**
     * Логгирование ответа сервиса
     */
    public function logResponse($response)
    {
        if (is_array($response) || is_object($response)) {
            $this->response = Json::encode($response);
        } else {
            $this->response = (string)$response;
        }
    }

    /**
     * Обновить статус доставки
     */
    public function markAsDelivered()
    {
        $this->status = self::STATUS_DELIVERED;
        return $this->save(false);
    }

    /**
     * Обновить статус прочтения
     */
    public function markAsRead()
    {
        $this->status = self::STATUS_READ;
        return $this->save(false);
    }

    /**
     * Зарегистрировать действие пользователя
     */
    public function registerAction($actionName, $data, $success = true)
    {
        $this->action_data = Json::encode([
            'action' => $actionName,
            'data' => $data,
            'timestamp' => time(),
        ]);

        $this->action_status = $success
            ? self::ACTION_SUCCESS
            : self::ACTION_FAILED;

        return $this->save(false);
    }

    /**
     * Поиск по получателю
     */
    public static function findByRecipient($recipient, $channel = null)
    {
        $query = self::find()->where(['recipient' => $recipient]);

        if ($channel) {
            $query->andWhere(['channel' => $channel]);
        }

        return $query->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Поиск по статусу
     */
    public static function findByStatus($status)
    {
        return self::find()->where(['status' => $status]);
    }

    /**
     * Поиск неудачных отправок
     */
    public static function findFailed()
    {
        return self::findByStatus(self::STATUS_FAILED);
    }

    /**
     * Поиск для повторной отправки
     */
    public static function findForRetry($hours = 24)
    {
        return self::findFailed()
            ->andWhere(['>', 'created_at', time() - $hours * 3600])
            ->andWhere(['<', 'retry_count', 3]); // Добавить поле retry_count в миграцию
    }
}