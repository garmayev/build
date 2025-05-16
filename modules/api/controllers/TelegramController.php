<?php

namespace app\modules\api\controllers;

use aki\telegram\base\Command;
use app\components\Helper;
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
            \Yii::error($args);
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
                \Yii::error([
                    "ok" => false,
                    "message" => "Missing args['order_id']"
                ]);
            }

            $order = Order::findOne($orderId);
            $coworker = Coworker::findOne(['chat_id' => $telegram->input->callback_query->from["id"]]);

            if (!$order || !$coworker) {
                \Yii::error([
                    "ok" => false,
                    "message" => "Missing {$coworker->name} or order #{$order->id}"
                ]);
            }

            // Add coworker to order
            if (!$order->isFull()) {
                if (!$order->assignCoworker($coworker)) {
                    \Yii::error(["ok" => false, "message" => "Coworker {$coworker->name} is agreed to order #{$order->id}"]);
                }

                $messages = TelegramMessage::find()->where(['order_id' => $order->id])->andWhere(['status' => TelegramMessage::STATUS_NEW])->all();

                // If order is now complete, update status
                if ($order->isFull()) {
                    $order->status = Order::STATUS_PROCESS;
                    $order->save();
                    if (YII_ENV === 'prod') {
                        foreach ($messages as $message) {
                            $message->remove();
                        }
                    } else {
                        return $messages;
                    }
                } else {
                    foreach ($messages as $message) {
                        $header = $message->status ?
                            \Yii::t('app', 'You have agreed to complete the order') . " #{$order->id}" :
                            \Yii::t('app', 'New Order') . " #{$order->id}";

                        // Для сотрудника, который согласился, убираем кнопки
                        $replyMarkup = null;
                        if ($message->chat_id === $coworker->chat_id) {
                            $replyMarkup = null; // Убираем кнопки
                        } else {
                            $replyMarkup = $message->reply_markup; // Оставляем существующие кнопки
                        }

                        $message->editMessageText(
                            $header . Helper::generateTelegramMessage($order->id),
                            $replyMarkup
                        );
                    }
                }
            }
        });

        // Handle /decline command
        Command::run("/decline", function ($telegram, $args) {
            $order = Order::findOne($args['order_id'] ?? null);
            if (!$telegram->input->callback_query) {
                return;
            }

            $messageId = $telegram->input->callback_query->message->message_id;
            $chatId = $telegram->input->callback_query->from["id"];

            $message = TelegramMessage::findOne([
                'message_id' => $messageId,
                'chat_id' => $chatId,
                'order_id' => $order->id
            ]);

            if ($message) {
                $message->remove();
            }
        });

        return [];
    }
}
