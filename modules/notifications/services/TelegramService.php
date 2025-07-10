<?php

namespace app\modules\notifications\services;

use app\modules\notifications\handlers\HandlerFactory;
use app\modules\notifications\models\Notification;

class TelegramService implements Service
{
    private $config;
    private $httpClient;
    private $commandMap;
    private static $instance;

    public function __construct($config)
    {
        $this->config = $config;
        $this->httpClient = new \yii\httpclient\Client();
        $this->commandMap = $config['commandMap'] ?? [];
    }

    public static function instance()
    {
        if (self::$instance === null) {
            $module = \Yii::$app->getModule('notifications');
            self::instance($module->get('telegram'));
        }
        return self::$instance;
    }

    public function send($notification)
    {
        $url = "https://api.telegram.org/bot{$this->config['token']}/sendMessage";
        $buttons = [];
        foreach ($notification->actions as $action) {
            $buttons[] = [
                [
                    'text' => $action->text,
                    'callback_data' => "action={$action['name']}&notification_id={$notification->id}",
                ]
            ];
        }
        $keyboard = ['inline_keyboard' => $buttons];
        $response = $this->httpClient->post($url, [
            'chat_id' => $notification->recipient,
            'text' => $notification->message,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($keyboard),
        ])->send();

        return [
            'success' => $response->isOk,
            'data' => $response->data,
        ];
    }

    public function handleCallback($data)
    {
        $result = false;

        // Обработка callback_query (инлайновые кнопки)
        if (isset($data['callback_query'])) {
            $result = $this->handleButtonPress($data['callback_query']);
        }

        // Обработка текстовых сообщений (команды)
        if (!$result && isset($data['message']['text'])) {
            $result = $this->handleCommand($data['message']);
        }

        return $result;
    }

    protected function handleCommand($message)
    {
        $text = trim($message['text']);
        $chatId = $message['chat']['id'];

        // Извлекаем команду (например, "/start" → "start")
        $command = preg_replace('/^\//', '', explode(' ', $text)[0]);

        if (isset($this->commandMap[$command])) {
            $commandClass = $this->commandMap[$command] ?? null;

            if (class_exists($commandClass)) {
                return $commandClass::run([
                    'chat_id' => $chatId,
                    'message' => $message,
                    'command' => $command,
                    'params' => array_slice(explode(' ', $text), 1) // Дополнительные параметры
                ]);
            }
        }

        return false;
    }

    protected function handleButtonPress($callbackQuery)
    {
        $callbackData = $this->parseCallbackData($callbackQuery['data']);
        $action = $callbackData['action'] ?? null;

        if ($action) {
            try {
                $handler = HandlerFactory::create($action);
                $params = [
                    'action' => $action,
                    'chat_id' => $callbackQuery['from']['id'],
                    'message_id' => $callbackQuery['message']['message_id'],
                    'data' => $callbackData
                ];
                $result = $handler->run($params);
                $this->answerCallbackQuery($callbackQuery['id']);
                return $result;
            } catch (\Exception $e) {
                \Yii::error($e->getMessage());
            }
        }

        return false;
    }

    private function parseCallbackData($data)
    {
        $result = [];
        parse_str($data, $result);
        return $result;
    }

    private function answerCallbackQuery($callbackId)
    {
        $url = "https://api.telegram.org/bot{$this->config['token']}/answerCallbackQuery";
        $this->httpClient->post($url, [
            'callback_query_id' => $callbackId,
        ])->send();
    }

    public function setWebhook()
    {
        $url = "https://api.telegram.org/bot{$this->config['token']}/setWebhook";
        $response = $this->httpClient->post($url, [
            'url' => $this->config['webhookUrl'],
        ])->send();
        return $response->data;
    }
}