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
                    \Yii::error("Model founded id = {$model->id}");
                    
                    $profile->chat_id = "{$telegram->input->message->from->id}";
                    $profile->family = $telegram->input->message->from->first_name;
                    $profile->name = $telegram->input->message->from->last_name;
                    $profile->phone = $contact["phone_number"];
                    if (!$profile->save()) {
                        \Yii::error($profile->errors);
                    } else {
                        \Yii::error("Profile saved");
                        \Yii::error($profile->attributes);
                        $telegram->sendMessage([
                            'chat_id' => $telegram->input->message->from->id,
                            'text' => \Yii::t('telegram', 'command_contact_complete'),
                            'reply_markup' => json_encode([
                                'keyboard' => []
                            ])
                        ]);
                        return ;
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
                        } else {
                            $telegram->sendMessage([
                                'chat_id' => $telegram->input->message->from->id,
                                'text' => \Yii::t('telegram', 'command_contact_complete'),
                                'reply_markup' => json_encode([
                                    'keyboard' => null
                                ])
                            ]);
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
                    $hour = \app\models\Hours::find()->where(['user_id' => $user->id])->andWhere(['date' => \Yii::$app->formatter->asDate(time(), 'php:Y-m-d')])->one();
                    if ($hour) {
                        $telegram->sendMessage([
                            'chat_id' => $telegram->input->message->from->id,
                            'text' => \Yii::t('telegram', 'command_hours_isset'),
                            'keyboard' => null
                        ]);
                        return ;
                    }
                    if ( Helper::isPointInCircle( $order->building->location->attributes, $telegram->input->message->location, $order->building->radius ) ) {
                        $hours = new \app\models\Hours(['order_id' => $order->id, 'user_id' => $user->id, 'is_payed' => 0, 'count' => 0, 'date' => \Yii::$app->formatter->asDate(time(), 'php:Y-m-d') ]);
                        if ($hours->save()) {
                            \Yii::$app->session->remove('order_id');
                            $telegram->sendMessage([
                                'chat_id' => $telegram->input->message->from->id,
                                'text' => \Yii::t('telegram', 'command_hours_created'),
                                'reply_markup' => [
                                    'keyboard' => null,
                                ],
                            ]);
                            \Yii::error('Hours saved');
                        } else {
                            $telegram->sendMessage([
                                'chat_id' => $telegram->input->message->from->id,
                                'text' => \Yii::t('telegram', 'command_location_missing'),
                            ]);
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

                $profile = \app\models\User::find()->joinWith('profile')->where(['profile.chat_id' => $chatId])->one();

                if ($profile) {
                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => \Yii::t('telegram', 'command_already_registered')
                    ]);
                } else {
                    $telegram->sendMessage([
                        'chat_id' => $telegram->input->message->from->id,
                        'text' => \Yii::t('telegram', 'command_start'),
                        'reply_markup' => json_encode([
                            'keyboard' => [
                                [['text' => \Yii::t('telegram', 'command_contact'), 'request_contact' => true]],
                            ],
                            'one_time_keyboard' => true,
                            'resize_keyboard' => true,
                        ])
                    ]);
                }
            });
            Command::run("/menu", function ($telegram) {
                $telegram->sendMessage([
                    'chat_id' => $telegram->input->message->from->id,
                    'text' => \Yii::t('app', 'command_menu'),
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            [['text' => \Yii::t('app', 'command_start_day'), 'callback_data' => '/start_day']],
                            [['text' => \Yii::t('app', 'command_orders_list'), 'callback_data' => '/order_list']],
                            [['text' => \Yii::t('app', 'command_orders_my'), 'callback_data' => '/my']]
                        ]
                    ])
                ]);
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
                    'text' => count($orders) ? \Yii::t('telegram', 'command_order_list') : \Yii::t('telegram', 'command_empty'),
                    'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
                ]);
            });

            Command::run("/start_day", function ($telegram) {
                $profile = \app\models\Profile::findOne(['chat_id' => $telegram->input->message->from->id]);
                $user = $profile->user;
                $keyboard = [];
                $hour = \app\models\Hours::find()
                    ->where(['user_id' => $user->id])
                    ->andWhere(['date' => \Yii::$app->formatter->asDate(time(), 'php:Y-m-d')])
                    ->one();
                if (empty($hour)) {
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
                } else {
                    $telegram->sendMessage([
                        'chat_id' => $telegram->input->message->from->id,
                        'text' => \Yii::t('app', 'command_hours_isset')
                    ]);
                }
            });
            Command::run("/accept", function ($telegram) {
                $user = \app\models\User::find()->joinWith('profile')->where(['profile.chat_id' => $telegram->input->message->from->id])->one();
                $keyboard = [];
                foreach ($user->suitableOrders as $order) {
                    $keyboard[] = [['text' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/view_order id='.$order->id]];
                }
                $telegram->sendMessage([
                    'chat_id' => $telegram->input->message->from->id,
                    'text' => (empty($keyboard)) ? \Yii::t('app', 'command_empty') : \Yii::t('app', 'command_order_list'),
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $keyboard
                    ])
                ]);
            });
            Command::run("/my", function ($telegram) {
                $user = \app\models\User::find()->joinWith('profile')->where(['profile.chat_id' => $telegram->input->message->from->id])->one();
                $keyboard = [];
                foreach ( $user->orders as $order ) {
                    $keyboard[] = [['text' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/view_order id='.$order->id]];
                }
                $telegram->sendMessage([
                    'chat_id' => $telegram->input->message->from->id,
                    'text' => (empty($keyboard)) ? \Yii::t('telegram', 'command_empty') : \Yii::t('telegram', 'command_order_list'),
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $keyboard
                    ])
                ]);
            });
        }
        if (isset($telegram->input->callback_query)) {
            session_id( $telegram->input->callback_query->from['id'] );
            Command::run("/view_order", function ($telegram, $args) {
                parse_str($args[0] ?? '', $data);
                $id = $data["id"] ?? null;
                \Yii::error($id);

                if (empty($id)) {
                    return null;
                }

                $telegram->editMessageText([
                    'chat_id' => $telegram->input->callback_query->from['id'],
                    'parse_mode' => 'html',
                    'message_id' => $telegram->input->callback_query->message['message_id'],
                    'text' => "<b>".\Yii::t("app", "Order #{id}", ['id' => $id])."</b>\n".\app\components\Helper::generateTelegramMessage($id),
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            [['text' => \Yii::t('telegram', 'command_decline'), 'callback_data' => '/decline order_id='.$id]],
                            [['text' => \Yii::t('telegram', 'command_back'), 'callback_data' => '/my']],
                        ]
                    ])
                ]);
            });
            Command::run("/my", function ($telegram) {
                $user = \app\models\User::find()->joinWith('profile')->where(['profile.chat_id' => $telegram->input->callback_query->from['id']])->one();
                $keyboard = [];
                foreach ( $user->orders as $order ) {
                    $keyboard[] = [['text' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/view_order id='.$order->id]];
                }
                $telegram->editMessageText([
                    'chat_id' => $telegram->input->callback_query->from['id'],
                    'message_id' => $telegram->input->callback_query->message['message_id'],
                    'text' => (empty($keyboard)) ? \Yii::t('app', 'command_empty') : \Yii::t('app', 'command_order_list'),
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $keyboard
                    ])
                ]);

            });
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
                // \Yii::error($order->isFull());
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
                            foreach ($messages as $message) {
                                $header = $message->chat_id == $coworker->profile->chat_id ?
                                    \Yii::t('app', 'You have agreed to complete the order') . " #{$order->id}\n" :
                                    \Yii::t('app', 'New Order') . " #{$order->id}\n";
    
                                // Для сотрудника, который согласился, убираем кнопки
                                \Yii::error($telegram->input->callback_query->from['id']);
                                \Yii::error($coworker->profile->chat_id);
                                if ($message->chat_id == $telegram->input->callback_query->from['id']) {
                                    \Yii::error('Keyboard must be empty');
                                    $replyMarkup = json_encode(['inline_keyboard' => []]);
                                } else {
                                    \Yii::error('Keyboard must isset');
                                    $replyMarkup = $message->reply_markup;
                                }
                                $text = "";
                                $message->editMessageText(
                                    $header . Helper::generateTelegramMessage($order->id),
                                    $replyMarkup
                                );
                            }
                        }
                    } else {
                        $telegram->editMessageText([
                            'message_id' => $telegram->input->callback_query->message['message_id'],
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
                $user = \app\models\User::find()->joinWith('profile')->where(['profile.chat_id' => $telegram->input->callback_query->from['id']])->one();

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

                $messageId = $telegram->input->callback_query->message['message_id'];
                $chatId = $telegram->input->callback_query->from['id'];

                if ( $order->revokeCoworker($user) ) {
                    \Yii::error("Coworker is revoked {$chatId} successfully");
                } else {
                    \Yii::error("Coworker is already removed {$chatId}");
                }

                $message = TelegramMessage::findOne([
                    'message_id' => $messageId,
                    'chat_id' => $chatId,
                    'order_id' => $order->id
                ]);

//                \Yii::error($messageId);
//                \Yii::error($chatId);

                if ($message) {
                    $message->remove();
                } else {
                    \Yii::error( $messageId );
                    $telegram->deleteMessage([
                        'chat_id' => $chatId,
                        'message_id' => $messageId,
                    ]);
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
                                ['text' => \Yii::t('app', 'Accept'), 'callback_data' => '/menu_accept order_id='.$order->id]
                            ], [
                                ['text' => \Yii::t('app', 'Decline'), 'callback_data' => '/menu_decline order_id='.$order->id]
                            ]
                        ]
                    ]
                );
            });
            Command::run("/menu_accept", function ($telegram) {

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
