<?php

namespace app\modules\api\commands;

use yii\base\Component;
use yii\base\InvalidConfigException;

class Command extends Component
{
    /**
     * Runs command for message text
     *
     * @param string $command The command to listen for (e.g. "/start")
     * @param string|array|callable $handler Class and method or function to handle the command
     * @return mixed|void
     * @throws InvalidConfigException
     */
    public static function onMessage($command, $handler)
    {
        $telegram = \Yii::$app->telegram;

        if (!isset($telegram->input->message)) {
            return;
        }
        try {
            $text = $telegram->input->message->text ?? '';
    
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
     * Runs handler for callback query
     *
     * @param string $callbackData The callback data to listen for
     * @param string|array|callable $handler Class and method or function to handle the callback
     * @return mixed|void
     * @throws InvalidConfigException
     */
    public static function onCallback($callbackData, $handler)
    {
        $telegram = \Yii::$app->telegram;

        if (!isset($telegram->input->callback_query)) {
            return;
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
            \Yii::error(json_decode(file_get_contents("php://input"), true));
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

        if (!isset($telegram->input->message) || !isset($telegram->input->message->location)) {
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
    protected static function callHandler($handler, $telegram, $args)
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