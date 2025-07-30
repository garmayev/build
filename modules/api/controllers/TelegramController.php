<?php

namespace app\modules\api\controllers;

use app\components\Command;
use app\components\Helper;
use app\models\forms\UserRegisterForm;
use app\models\Profile;
use app\models\telegram\TelegramMessage;
use app\models\User;
use app\models\Order;

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
        if (isset($telegram->input->message)) {
            session_id( $telegram->input->message->from->id );
            if (isset($telegram->input->message->contact)) {
                $contact = $telegram->input->message->contact;
                $phone = preg_replace("/[\(\)\+\ \-]/", "", $contact["phone_number"]);
                $profile = Profile::findOne(['phone' => $phone]);
                if (empty($profile)) {
                    $model = User::findOne(['username' => $phone]);
                } else {
                    $model = $profile->user;
                }
                if ($model) {
                    $model->profile->chat_id = "{$contact["user_id"]}";
                    if (!$model->profile->save()) {
                        \Yii::error($model->profile->errors);
                    }
                } else {
                    $model = new UserRegisterForm([
                        "username" => $phone,
                        "email" => "{$phone}@t.me",
                        "new_password" => $phone,
                        "level" => User::PRIORITY_LOW,
                    ]);
                    if ($model->validate() && $model->register()) {
                        $user = User::findOne(['username' => $phone]);
                        $user->profile->load(['Profile' => [
                            'family' => $contact['last_name'],
                            'name' => $contact["first_name"],
                            'phone' => $phone,
                            'chat_id' => "{$contact['user_id']}",
                        ]]);
                        if (!$user->profile->save()) {
                            \Yii::error($user->profile->getErrors());
                        }
                    } else {
                        \Yii::error($model->getErrors());
                    }
                    $model = $user;
                }
                if (!$model->can("employee")) {
                    \Yii::$app->authManager->assign(\Yii::$app->authManager->getRole("employee"), $model->id);
                }
                return $model;
            }

            if ($telegram->input->message->location) {
                $order = Order::findOne(\Yii::$app->session->get('order_id'));
                $user = \app\models\User::findByChatId( $telegram->input->message->from->id );
                if (isset($order)) {
                    if ( Helper::isPointInCircle( $order->building->location->attributes, $telegram->input->message->location, $order->building->radius ) ) {
                        $hours = new \app\models\Hours(['order_id' => $order->id, 'user_id' => $user->id, 'is_payed' => 0, 'count' => 0, 'date' => \Yii::$app->formatter->asDate(time(), 'php:Y-m-d') ]);
                        if ($hours->save()) {
                            \Yii::$app->session->remove('order_id');
                            $telegram->sendMessage([
                                'chat_id' => $telegram->input->message->from->id,
                                'text' => \Yii::t('app', 'command_hours_created'),
                                'keyboard' => json_encode([
                                    'keyboard' => []
                                ]),
                            ]);
                        } else {
                            \Yii::error($hours->errors);
                        }
                    }
                }
            }

            // Handle /start command
            Command::run("/start", function ($telegram) {
                $chatId = $telegram->input->message ? $telegram->input->message->from->id : null;
                if (!$chatId) {
                    return;
                }

                if (isset($args) && isset($args[0])) {
                    $coworker = Profile::findOne($args[0]);
                } else {
                    // Find coworker without chat_id or with this chat_id
                    $coworker = Profile::find()
                        ->where(['chat_id' => $chatId])
                        ->one();
                }

                if ($coworker) {
                    $coworker->chat_id = "$chatId";
                    if ($coworker->save()) {
                        $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => 'Вы успешно подключены к системе уведомлений!'
                        ]);
                    } else {
                        \Yii::error($coworker->errors);
                    }
                }
            });

            Command::run("/order_list", function ($telegram) {
                $user = User::findByChatId($telegram->input->message->from->id);
                if ( empty($user) ) {
                    \Yii::error( "Unknown user" );
                    return null;
                }
//                \Yii::error($user->attributes);
                $keyboard = [];
                $orders = $user->getSuitableOrders();
                foreach ($orders as $order) {
                    $keyboard[] = [['text' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/order_detail id='.$order->id]];
                }
                $telegram->sendMessage([
                    'chat_id' => $telegram->input->message->from->id,
                    'text' => count($orders) ? \Yii::t('app', 'command_order_list') : \Yii::t('app', 'command_empty'),
                    'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
                ]);
            });

            Command::run("/start_day", function ($telegram) {
                $profile = \app\models\Profile::findOne(['chat_id' => $telegram->input->message->from->id]);
                $user = $profile->user;
                $keyboard = [];
                foreach ($user->orders as $order) {
                    $keyboard[] = [['text' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/order id='.$order->id]];
                }
//                \Yii::error($keyboard);
                $telegram->sendMessage([
                    'chat_id' => $telegram->input->message->from->id,
                    'text' => (empty($keyboard)) ? \Yii::t('app', 'command_empty') : \Yii::t('app', 'command_order_list'),
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $keyboard,
//                        'one_time_keyboard' => true,
//                        'resize_keyboard' => true,
                    ])
                ]);
            });
        }
        if (isset($telegram->input->callback_query)) {
            session_id( $telegram->input->callback_query->from['id'] );
            Command::run("/order", function ($telegram, $args) {
                parse_str($args[0] ?? '', $data);
                $id = $data["id"] ?? null;
                if (empty($id)) {
                    return null;
                }
                $order = \app\models\Order::findOne($id);
                \Yii::$app->session->set('order_id', $id);
/*                $telegram->answerCallbackQuery([
                    'callback_query_id' => $telegram->input->callback_query->id,
                ]);
                \Yii::error(  );
                $telegram->deleteMessage([
                    'chat_id' => $telegram->input->callback_query->from['id'],
                    'message_id' => $telegram->input->callback_query->message['message_id'],
                ]); */
                $telegram->sendMessage([
                    'chat_id' => $telegram->input->callback_query->from['id'],
                    'text' => \Yii::t('app', 'command_location'),
                    'reply_markup' => json_encode([
                        'keyboard' => [
                            [
                                ['text' => \Yii::t('app', 'command_send_location'), 'request_location' => true]
                            ]
                        ],
                        'one_time_keyboard' => true,
                        'resize_keyboard' => true,
                    ])
                ]);
//                \Yii::error( $order->attributes );
                return null;
            });
            // Handle /agree command
            Command::run("/accept", function ($telegram, $args) {
                if (!$telegram->input->callback_query) {
                    return null;
                }

                parse_str($args[0] ?? '', $data);
                $orderId = $data["order_id"] ?? null;

                if (!$orderId) {
                    \Yii::error([
                        "ok" => false,
                        "message" => "Missing args['order_id']"
                    ]);
                    return ['ok' => false];
                }

                $order = Order::findOne($orderId);
                $coworker = User::find()->joinWith('profile')->where(['profile.chat_id' => $telegram->input->callback_query->from["id"]]);
                $coworker = $coworker->one();

                if (!$order || !$coworker) {
                    \Yii::error([
                        "ok" => false,
                        "message" => "Missing {$coworker->name} or order #{$order->id}"
                    ]);
                }

                // Add coworker to order
//                \Yii::error($order->isFull());
                if (!$order->isFull()) {
                    if (!$order->assignCoworker($coworker)) {
                        \Yii::error(["ok" => false, "message" => "Coworker {$coworker->name} is already agreed to order #{$order->id}"]);
                        return null;
                    }

                    $messages = TelegramMessage::find()->where(['order_id' => $order->id])->all();
                    if (count($messages)) {
                        // If order is now complete, update status
                        if ($order->isFull()) {
                            $order->status = Order::STATUS_PROCESS;
                            $order->save();
                            if (YII_ENV === 'prod') {
                                foreach ($messages as $message) {
                                    if (in_array($message->chat_id, array_merge(\yii\helpers\ArrayHelper::map($order->coworkers, 'profile.chat_id', 'profile.chat_id'), [$order->owner->profile->chat_id => $order->owner->profile->chat_id]))) {
                                        $message->editMessageText(Helper::generateTelegramHiddenMessage($order->id), null);
                                    } else {
                                        $message->remove();
                                    }
                                }
                                if ($order->owner->profile->chat_id) {
                                    $message->editMessageText(Helper::generateTelegramHiddenMessage($order->id), null);
                                }
                            } else {
                                return $messages;
                            }
                        } else {
                            foreach ($messages->all() as $message) {
                                $header = $message->chat_id == $coworker->profile->chat_id ?
                                    \Yii::t('app', 'You have agreed to complete the order') . " #{$order->id}\n" :
                                    \Yii::t('app', 'New Order') . " #{$order->id}\n";
    
                                // Для сотрудника, который согласился, убираем кнопки
                                $replyMarkup = null;
                                $text = "";
                                if ($message->chat_id == $coworker->profile->chat_id) {
                                    $replyMarkup = []; // Убираем кнопки
                                } else {
                                    $replyMarkup = json_decode($message->reply_markup); // Оставляем существующие кнопки
                                }
    
                                $message->editMessageText(
                                    $header . Helper::generateTelegramMessage($order->id),
                                    $replyMarkup
                                );
                            }
                        }
                    } else {
                        $telegram->editMessageText([
                            'message_id' => $telegram->input->message->id,
                            'text' => Helper::orderDetails(),
                            'reply_markup' => null,
                        ]);
                    }
                }
                return null;
            });

            // Handle /decline command
            Command::run("/decline", function ($telegram, $args) {
                parse_str($args[0] ?? '', $data);
                $orderId = $data["order_id"] ?? null;

                if (!$orderId) {
                    \Yii::error([
                        "ok" => false,
                        "message" => "Missing args['order_id']"
                    ]);
                }

                $order = Order::findOne($orderId ?? null);
                if (!$telegram->input->callback_query) {
                    return;
                }

                $messageId = $telegram->input->callback_query->message->message_id;
                $chatId = $telegram->input->callback_query->from->id;

                $message = TelegramMessage::findOne([
                    'message_id' => $messageId,
                    'chat_id' => $chatId,
                    'order_id' => $order->id
                ]);

                if ($message) {
                    $message->remove();
                }
            });
            Command::run("/order_detail", function ($telegram, $args) {
                parse_str($args[0] ?? '', $data);
                $id = $data['id'] ?? null;
                if (empty($id)) { return null; }

                $order = Order::findOne($id);
                $user = User::findByChatId($telegram->input->callback_query->from['id']);
                Helper::notify(
                    $user->id,
                    $order->id,
                    null,
                    [
                        'inline_keyboard' => [
                            [
                                ['text' => \Yii::t('app', 'Accept'), 'callback_data' => '/accept order_id='.$order->id]
                            ], [
                                ['text' => \Yii::t('app', 'Decline'), 'callback_data' => '/decline order_id='.$order->id]
                            ]
                        ]
                    ]
                );
//                    new TelegramMessage([
//                    'chat_id' => $telegram->input->callback_query->from['id'],
//                    'text' => Helper::orderDetails($order),
//                    'parse_mode' => 'html',
//                    'reply_markup' => json_encode([
//                        'inline_keyboard' => [
//                            [
//                                ['text' => \Yii::t('app', 'Accept'), 'callback_data' => '/accept order_id=' . $order->id],
//                            ], [
//                                ['text' => \Yii::t('app', 'Decline'), 'callback_data' => '/decline order_id=' . $order->id],
//                            ]
//                        ]
//                    ]),
//                ]);
//                $telegram->sendMessage([
//                ]);
            });
            Command::run("/start", function ($telegram, $args) {

            });
        }
        return [];
    }

    private function getProfile($data)
    {
        $phone = preg_replace("/[\(\)\+\ \-]/", "", $data["phone_number"]);
        $profile = \app\models\Profile::findOne(["phone" => $phone]);
        return $profile;
    }
}
