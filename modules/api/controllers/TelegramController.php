<?php

namespace app\modules\api\controllers;

use aki\telegram\base\Command;
use app\models\Coworker;
use app\models\telegram\TelegramMessage;
use app\models\User;
use app\models\Order;
use app\models\Telegram;
use yii\helpers\ArrayHelper;

class TelegramController extends \yii\web\Controller
{
    private $query;
    private $params;

    public function beforeAction($action)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionCallback()
    {
        $telegram = \Yii::$app->telegram;
        
        // Handle /start command
        Command::run("/start", function ($telegram, $args) {
            $chatId = $telegram->input->message ? $telegram->input->message->from->id : null;
            if (!$chatId) {
                return;
            }
            
            // Find coworker without chat_id or with this chat_id
            $coworker = Coworker::find()
                ->where(['or', ['chat_id' => null], ['chat_id' => $chatId]])
                ->one();
                
            if ($coworker) {
                $coworker->chat_id = $chatId;
                if ($coworker->save()) {
                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Вы успешно подключены к системе уведомлений!'
                    ]);
                }
            }
        });

        // Handle /agree command
        Command::run("/agree", function ($telegram, $args) {
            if (!$telegram->input->callback_query) {
                return;
            }

            parse_str($args[0] ?? '', $data);
            $orderId = $data["order_id"] ?? null;
            if (!$orderId) {
                return;
            }

            $order = Order::findOne($orderId);
            $coworker = Coworker::findOne(['chat_id' => $telegram->input->callback_query->from["id"]]);
            
            if (!$order || !$coworker) {
                return;
            }

            // Add coworker to order
            if (!$order->checkSuccessfully()) {
                $order->link('coworkers', $coworker);
                
                // If order is now complete, update status
                if ($order->checkSuccessfully()) {
                    $order->status = Order::STATUS_PROCESS;
                    $order->save();
                }

                // Update all messages for this order
                $messages = TelegramMessage::find()->where(['order_id' => $order->id])->all();
                foreach ($messages as $message) {
                    $header = $message->status ? 
                        \Yii::t('app', 'You have agreed to complete the order') . " #{$order->id}" :
                        \Yii::t('app', 'New Order') . " #{$order->id}";
                    
                    // Добавляем информацию о количестве сотрудников
                    $totalRequired = $order->requiredCoworkers;
                    $currentCount = $order->issetCoworkers;
                    $header .= "\n" . sprintf("Требуется сотрудников: %d из %d", $currentCount, $totalRequired);
                    
                    // Для сотрудника, который согласился, убираем кнопки
                    $replyMarkup = null;
                    if ($message->chat_id === $coworker->chat_id) {
                        $replyMarkup = null; // Убираем кнопки
                    } else {
                        $replyMarkup = $message->reply_markup; // Оставляем существующие кнопки
                    }
                        
                    $message->editText(
                        $replyMarkup,
                        $order->generateTelegramText($header)
                    );
                }

                // If order is complete, remove messages for non-participating coworkers
                if ($order->checkSuccessfully()) {
                    $participatingChatIds = ArrayHelper::map($order->coworkers, 'chat_id', 'chat_id');
                    foreach ($messages as $message) {
                        if (!in_array($message->chat_id, $participatingChatIds)) {
                            $message->remove();
                        }
                    }
                }
            }
        });

        // Handle /decline command
        Command::run("/decline", function ($telegram, $args) {
            if (!$telegram->input->callback_query) {
                return;
            }

            $messageId = $telegram->input->callback_query->message->message_id;
            $chatId = $telegram->input->callback_query->from["id"];

            $message = TelegramMessage::findOne([
                'message_id' => $messageId,
                'chat_id' => $chatId
            ]);

            if ($message) {
                $message->remove();
            }
        });

        return [];
    }
}
