Update 1
Использование модуля

Добавьте в конфиг приложения:

```php
'modules' => [
    'notifications' => [
        'class' => 'app\modules\notifications\Module',
        'telegramConfig' => [
            'botToken' => 'YOUR_TELEGRAM_BOT_TOKEN',
            'webhookUrl' => 'https://yourdomain.com/notifications/webhook/telegram'
        ],
        'fcmConfig' => [
            'apiKey' => 'YOUR_FCM_API_KEY',
            'senderId' => 'YOUR_SENDER_ID'
        ],
        'apnsConfig' => [
            'certificatePath' => '@app/certificates/apns.pem',
            'passphrase' => '',
            'environment' => 'production'
        ]
    ]
],
```

Пример отправки уведомления:

```php
use app\modules\notifications\models\Notification;
use app\modules\notifications\services\NotificationService;

$notification = new Notification();
$notification->title = 'Новый заказ';
$notification->message = 'У вас новый заказ #1234';
$notification->recipient = 'TELEGRAM_CHAT_ID_OR_FCM_TOKEN_OR_APNS_TOKEN';
$notification->channels = [Notification::CHANNEL_TELEGRAM, Notification::CHANNEL_FCM];
$notification->actions = [
    [
        'name' => 'accept_order',
        'title' => 'Принять заказ',
        'data' => ['order_id' => 1234]
    ],
    [
        'name' => 'decline_order',
        'title' => 'Отклонить',
        'data' => ['order_id' => 1234]
    ]
];

/** @var NotificationService $service */
$service = Yii::$container->get(NotificationService::class);
$results = $service->send($notification);
```

Особенности реализации:
1. Поддержка нескольких каналов: Telegram, FCM (Android), APNs (iOS)

2. Обработка действий:

- Для Telegram через callback_query
- Для FCM через data-поле в уведомлении
- Для APNs через custom payload
- Логирование: Все отправки сохраняются в лог
- Конфигурируемость: Настройки каждого сервиса вынесены в конфиг модуля
- Гибкость действий: Поддержка нескольких кнопок с произвольными действиями

3. Для обработки действий вам нужно будет:

- Для Telegram: настроить вебхук (/notifications/set-telegram-webhook)
- Для FCM/APNs: настроить обработку deep links в мобильных приложениях, которые будут вести на /notifications/callback/{channel}

Этот модуль предоставляет базовую структуру, которую вы можете расширять под свои нужды, добавляя:

- Очередь отправки уведомлений
- Шаблоны сообщений
- Статистику доставки
- Поддержку других каналов (SMS, Email и т.д.)
- Более сложную логику обработки действий

Update 2
 
Пример использования модели NotificationLog

```php
// Создание лога после отправки
$log = new NotificationLog();
$log->notification_id = $notification->id;
$log->channel = 'telegram';
$log->recipient = '123456789';
$log->status = NotificationLog::STATUS_SENT;
$log->logResponse([
'message_id' => 456,
'date' => time(),
'text' => 'Message text'
]);
$log->save();

// Обновление статуса при доставке
$log->markAsDelivered();

// Регистрация действия пользователя
$log->registerAction('accept_order', [
'order_id' => 1234,
'user_id' => 5678
], true);

// Поиск логов для пользователя
$userLogs = NotificationLog::findByRecipient('user_token', 'fcm')->all();

// Повторная отправка неудачных уведомлений
$failedLogs = NotificationLog::findForRetry(48)->all();
foreach ($failedLogs as $log) {
$log->retry_count++;
try {
// Повторная отправка
$result = Yii::$app->notificationService->resend($log);
$log->status = NotificationLog::STATUS_SENT;
} catch (\Exception $e) {
$log->logResponse($e->getMessage());
}
$log->save();
}
```

Эта модель предоставляет мощный инструмент для:

Отслеживания истории отправки уведомлений

Анализа доставки и прочтения

Регистрации пользовательских действий

Повторной отправки неудачных уведомлений

Сбора статистики по различным каналам

Вы можете легко интегрировать эту модель в административный интерфейс для мониторинга уведомлений и анализа эффективности коммуникации.

Update 3

Преимущества данного подхода:
1. Простой интерфейс:

```php
Yii::$app->notificationService->send(...)
```

2. Поддержка массовой рассылки:

```php
->send([$user1, $user2], ...)
```

3. Централизованная обработка действий:

```php
->registerActionHandler('action_name', $callback)
```

- Автоматическая обработка ошибок:

- Валидация параметров

- Логирование ошибок

- Обработка исключений

4. Единая точка конфигурации:

Все настройки каналов хранятся в конфиге модуля

5. Расширяемость:

Легко добавить новые каналы или типы уведомлений

Этот компонент предоставляет удобный, объектно-ориентированный способ работы с уведомлениями, сохраняя при этом гибкость и расширяемость модуля уведомлений.