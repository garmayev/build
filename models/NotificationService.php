<?php

namespace app\models;

use app\models\telegram\TelegramMessage;
use ExpoSDK\Expo;
use ExpoSDK\ExpoMessage;
use Yii;
use yii\base\Component;
use yii\helpers\Json;

/**
 * NotificationService handles sending and updating notifications through Telegram and Push notifications
 */
class NotificationService extends Component
{
    /**
     * @var string Bot token from Telegram
     */
    public $botToken;

    /**
     * @var string Base URL for Telegram API
     */
    protected $telegramApiUrl = 'https://api.telegram.org/bot';

    /**
     * Initializes the component with configuration
     */
    public function init()
    {
        parent::init();
        $this->botToken = Yii::$app->params['bot_id'] ?? null;
        if (!$this->botToken) {
            Yii::error('Telegram bot token not configured');
        }
    }

    /**
     * Sends both Telegram and Push notifications for an order
     * 
     * @param Order $order The order to send notifications for
     * @param string $message The message to send
     * @param array $keyboard Optional keyboard markup for Telegram
     * @return array Array of notification results
     */
    public function sendOrderNotifications($order, $message, $keyboard = [])
    {
        $results = [];
        
        // Send to each coworker
        foreach ($order->getSuitableCoworkers() as $coworker) {
            // Send Telegram message if chat_id exists
            if ($coworker->chat_id) {
                $results['telegram'][] = $this->sendTelegramMessage($coworker->chat_id, $message, $keyboard, $order->id);
            }
            
            // Send push notification if device_id exists
            if ($coworker->device_id) {
                $results['push'][] = $this->sendPushNotification($coworker->device_id, [
                    'title' => Yii::t('app', 'New Order Notification'),
                    'body' => $message,
                    'data' => ['order_id' => $order->id]
                ]);
            }
        }
        
        return $results;
    }

    /**
     * Sends a Telegram message and stores it in the database
     * 
     * @param string $chatId Telegram chat ID
     * @param string $message Message text
     * @param array $keyboard Optional keyboard markup
     * @param int|null $orderId Related order ID
     * @return TelegramMessage|null
     */
    public function sendTelegramMessage($chatId, $message, $keyboard = [], $orderId = null)
    {
        try {
            $telegramMessage = new TelegramMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'reply_markup' => !empty($keyboard) ? Json::encode(['inline_keyboard' => $keyboard]) : '',
                'order_id' => $orderId,
                'status' => TelegramMessage::STATUS_NEW,
                'created_at' => time(),
                'updated_at' => time()
            ]);

            if ($telegramMessage->send()) {
                return $telegramMessage;
            }
        } catch (\Exception $e) {
            Yii::error("Error sending Telegram message: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Updates an existing Telegram message
     * 
     * @param TelegramMessage $message The message to update
     * @param string $newText New message text
     * @param array $keyboard Optional new keyboard markup
     * @return bool Success status
     */
    public function updateTelegramMessage($message, $newText, $keyboard = [])
    {
        try {
            $message->text = $newText;
            if (!empty($keyboard)) {
                $message->reply_markup = Json::encode(['inline_keyboard' => $keyboard]);
            }
            $message->updated_at = time();
            
            return $message->editText();
        } catch (\Exception $e) {
            Yii::error("Error updating Telegram message: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sends a push notification using Expo
     * 
     * @param string $deviceId Expo push token
     * @param array $data Notification data (title, body, data)
     * @return bool Success status
     */
    public function sendPushNotification($deviceId, $data)
    {
        try {
            $expo = new Expo();
            $message = new ExpoMessage([
                'title' => $data['title'],
                'body' => $data['body'],
                'data' => $data['data'] ?? [],
            ]);

            $response = $expo->send($message)->to($deviceId)->push();
            return !empty($response);
        } catch (\Exception $e) {
            Yii::error("Error sending push notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Removes a Telegram message
     * 
     * @param TelegramMessage $message The message to remove
     * @return bool Success status
     */
    public function removeTelegramMessage($message)
    {
        try {
            return $message->remove() !== false;
        } catch (\Exception $e) {
            Yii::error("Error removing Telegram message: " . $e->getMessage());
            return false;
        }
    }
} 