<?php

namespace app\modules\api\commands;

use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\web\Session;

class Command extends Component
{
    /**
     * @var Session Сессия для хранения данных
     */
    private static $session;

    /**
     * Инициализация сессии
     * @param int|null $chatId
     */
    private static function initSession(int|null $chatId = null)
    {
        if (self::$session === null) {
            self::$session = \Yii::$app->session;
            if (!self::$session->isActive) {
                self::$session->open($chatId);
            }
        }
    }

    /**
     * Runs command for message text
     *
     * @param string $command The command to listen for (e.g. "/start")
     * @param string|array|callable $handler Class and method or function to handle the command
     * @return mixed|void
     * @throws InvalidConfigException
     */
    public static function onMessage(string $command, $handler, $contextKey = null)
    {
        $telegram = \Yii::$app->telegram;
        if (!isset($telegram->input->message)) {
            return;
        }
        try {
            $text = $telegram->input->message->text ?? '';
            $chatId = $telegram->input->message->chat->id;
            if (isset($telegram->input->message)) {
                \Yii::$app->session->close();
                \Yii::$app->session->setId($telegram->input->message->from->id);
                \Yii::$app->session->open();
            }

            self::initSession();
            $currentContext = self::$session->get('command_context_' . $chatId, null);

            // Если есть активный контекст
            if ($currentContext !== null) {
                if ($currentContext['key'] === $command) {
                    // Сохраняем ответ, если указан contextKey
                    if ($contextKey !== null) {
                        self::saveResponse($chatId, $contextKey, $text);
                    }

                    self::$session->remove('command_context_' . $chatId);

                    return static::callHandler($handler, $telegram, $text);
                }
                return ;
            }

            if (!empty($text)) {
                $args = explode(' ', $text);
                $inputCommand = array_shift($args);
                if ($inputCommand === $command) {
                    return static::callHandler($handler, $telegram, $args);
                }
            }
        } catch (\Exception $e) {
            \Yii::error(json_decode(file_get_contents("php://input"), true));
            \Yii::error($e);
        }
    }

    /**
     * Обрабатывает контекстные ответы пользователя
     * 
     * @param \aki\telegram\Telegram $update
     * @return mixed|void
     */
    public static function handleContextResponse(\aki\telegram\Telegram $update)
    {
        $text = $update->message->text ?? '';
        if (empty($update->message)) {
            return;
        }
        $chatId = $update->message->chat->id;

        self::initSession($chatId);
        $currentContext = self::$session->get('command_context_' . $chatId, null);

        if ($currentContext === null) {
            \Yii::error('Context missing');
            return; // Нет активного контекста
        }

        // Найдите обработчик для текущего контекста
        $contextKey = $currentContext['key'];
        $handler = self::getHandlerForContext($contextKey);

        if ($handler) {
            // Сохраняем ответ, если указан contextKey
            if (isset($handler['contextKey'])) {
                \Yii::error('Context Saved');
                self::saveResponse($chatId, $handler['contextKey'], $text);
            }

            \Yii::error('Context removed');
            // Очищаем контекст после получения ответа
            self::$session->remove('command_context_' . $chatId);

            // Вызываем обработчик
            return static::callHandler($handler['handler'], $update, [$text]);
        }
    }

    /**
     * Возвращает обработчик для указанного контекста
     * 
     * @param string $contextKey
     * @return array|null
     */
    private static function getHandlerForContext(string $contextKey)
    {
        // Здесь должна быть логика поиска обработчика по ключу контекста
        // Например, можно хранить зарегистрированные обработчики в статическом массиве
        return self::$contextHandlers[$contextKey] ?? null;
    }

    /**
     * Регистрирует обработчик для контекста
     * 
     * @param string $contextKey Ключ контекста
     * @param string|array|callable $handler Обработчик
     * @param string|null $saveKey Ключ для сохранения ответа
     */
    public static function onContext(string $contextKey, $handler, string $saveKey = null)
    {
        self::$contextHandlers[$contextKey] = [
            'handler' => $handler,
            'contextKey' => $saveKey
        ];
    }

    /**
     * Сохраняем ответ пользователя в сессию
     *
     * @param int $chatId ID чата
     * @param string $key Ключ для сохранения
     * @param mixed $value Значение
     */
    public static function saveResponse(int $chatId, string $key, $value)
    {
        self::initSession($chatId);
        \Yii::error($value);
        $responses = self::$session->get('command_responses_'.$chatId, []);
        $responses[$key] = $value;
        self::$session->set('command_responses_'.$chatId, $responses);
    }

    /**
     * Получает сохраненный ответ пользователя
     *
     * @param int $chatId ID чата
     * @param string $key Ключ для получения
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public static function getResponse(int $chatId, string $key, $default = null)
    {
        self::initSession($chatId);
        $rtesponses = self::$session->get('command_responses_'.$chatId, []);
        return $responses[$key] ?? $default;
    }

    /**
     * Устанавливает контекст ожидания ответа
     * 
     * @param int $chatId ID чата
     * @param string $contextKey Ключ контекста
     * @param array $contextData Дополнительные данные
     */
    public static function expectResponse(int $chatId, string $contextKey, array $contextData = [])
    {
        self::initSession($chatId);
        self::$session->set('command_context_' . $chatId, [
            'key' => $contextKey,
            'data' => $contextData,
            'timestamp' => time()
        ]);
        \Yii::error('context set');
    }

    /**
     * Runs handler for callback query
     *
     * @param string $callbackData The callback data to listen for
     * @param string|array|callable $handler Class and method or function to handle the callback
     * @return mixed|void
     * @throws InvalidConfigException
     */
    public static function onCallback(string $callbackData, $handler)
    {
        $telegram = \Yii::$app->telegram;

        if (!isset($telegram->input->callback_query)) {
            return;
        } else {
            \Yii::$app->session->close();
            \Yii::$app->session->setId($telegram->input->callback_query->from['id']);
            \Yii::$app->session->open();
        }

        try {
            $data = $telegram->input->callback_query->data ?? '';

            if (!empty($data)) {
                $args = explode(' ', $data);
                $inputCallback = array_shift($args);

                if ($inputCallback === $callbackData) {
                    return static::callHandler($handler, $telegram, $args);
                }
            }
        } catch (\Exception $e) {
//            \Yii::error(json_decode(file_get_contents("php://input"), true));
            \Yii::error($e);
        }
    }

    /**
     * Runs handler for contact message
     *
     * @param string|array|callable $handler Class and method or function to handle the contact
     * @return mixed|void
     * @throws InvalidConfigException
     */
    public static function onContact($handler)
    {
        $telegram = \Yii::$app->telegram;

        if (!isset($telegram->input->message) || !isset($telegram->input->message->contact)) {
            return;
        }

        return static::callHandler($handler, $telegram, []);
    }

    /**
     * Runs handler for location message
     *
     * @param string|array|callable $handler Class and method or function to handle the location
     * @return mixed|void
     * @throws InvalidConfigException
     */
    public static function onLocation($handler)
    {
        $telegram = \Yii::$app->telegram;

        if (isset($telegram->input->message)) {
            \Yii::$app->session->close();
            \Yii::$app->session->setId($telegram->input->message->from->id);
            \Yii::$app->session->open();
        }

        if (!isset($telegram->input->message) || !isset($telegram->input->message->location)) {
            return;
        }

        return static::callHandler($handler, $telegram, []);
    }

    public static function onPhoto($handler)
    {
        $telegram = \Yii::$app->telegram;

        if (isset($telegram->input->message)) {
            \Yii::$app->session->close();
            \Yii::$app->session->setId($telegram->input->message->from->id);
            \Yii::$app->session->open();
        }

        if (!isset($telegram->input->message) || !isset($telegram->input->message->photo)) {
            return;
        }

        return static::callHandler($handler, $telegram, []);
    }

    /**
     * Calls the handler method
     *
     * @param string|array|callable $handler Class and method or function to call
     * @param mixed $telegram Telegram instance
     * @param array $args Command arguments
     * @return mixed
     * @throws InvalidConfigException
     */
    protected static function callHandler($handler, $telegram, array $args)
    {
        if (is_callable($handler)) {
            return call_user_func_array($handler, [$telegram, $args]);
        }

        if (is_string($handler)) {
            $handler = [$handler, 'handle'];
        }

        if (!is_array($handler) || count($handler) !== 2) {
            throw new InvalidConfigException('Handler must be a callable or array with class and method.');
        }

        list($className, $methodName) = $handler;

        if (!class_exists($className)) {
            throw new InvalidConfigException("Handler class '{$className}' does not exist.");
        }

        $instance = \Yii::createObject($className);

        if (!method_exists($instance, $methodName)) {
            throw new InvalidConfigException("Method '{$methodName}' does not exist in class '{$className}'.");
        }

        return call_user_func_array([$instance, $methodName], [$telegram, $args]);
    }
}