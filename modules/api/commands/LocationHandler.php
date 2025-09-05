<?php

namespace app\modules\api\commands;

use app\modules\api\commands\command\BaseCommand;

class LocationHandler extends BaseCommand implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $message = $telegram->input->message;
        $location = $message->location;
        $user = \app\models\User::findByChatId($message->from->id);
        $session = \Yii::$app->session;
        session_id($message->from->id);
        session_start();
        $orderId = $session->get("order_id");
        $order = \app\models\Order::findOne( $orderId );

        if ( empty($orderId) || empty($order) ) {
            $telegram->sendMessage([
                'chat_id' => $message->from->id,
                'text' => \Yii::t("telegram", "command_missing_order_id"),
            ]);
            return ;
        }

        $building = $order->building;

        if ( \app\components\Helper::isPointInCircle($building->location->attributes, $location, $building->radius) ) {
            $hours = new \app\models\Hours([
                'user_id' => $user->id, 
                'order_id' => $orderId, 
                'count' => 0, 
                'is_payed' => 0, 
                'date' => \Yii::$app->formatter->asDate(time(), 'php:Y-m-d'),
                'start_time' => \Yii::$app->formatter->asDatetime(time(), 'php:Y-m-d H:i:s'),
            ]);
            if ($hours->save()) {
                $telegram->sendMessage([
                    'chat_id' => $message->from->id,
                    'text' => \Yii::t("telegram", "command_hours_created"),
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            [['text' => \Yii::t('telegram', 'button_menu'), 'callback_data' => '/menu']]
                        ]
                    ])
                ]);
            } else {
                $telegram->sendMessage([
                    'chat_id' => $message->from->id,
                    'text' => \Yii::t("telegram", "command_hours_errors"),
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            [['text' => \Yii::t('telegram', 'button_menu'), 'callback_data' => '/menu']]
                        ]
                    ])
                ]);
            }
        } else {
            $telegram->sendMessage([
                'chat_id' => $message->from->id,
                'text' => \Yii::t("telegram", "command_location_missing"),
                'reply_markup' => json_encode([
                    'inline_markup' => [
                        [['text' => \Yii::t('telegram', 'button_menu'), 'callback_data' => '/menu']]
                    ]
                ])
            ]);
        }
        $session->remove('order_id');
    }
}