<?php

namespace app\modules\api\commands\max;

use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\MessageBuilder;

class ContactHandler implements BotHandler
{
    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onMessage(function ($request) use ($max) {
            if (isset($request->message->body->attachments)) {
                foreach ($request->message->body->attachments ?? [] as $attachment) {
                    if ($attachment->type === 'contact') {
                        try {
                            $info = $attachment->payload->getVcf_info();
                            $phone = $info["phones"][0]["number"];
                            $user = \app\models\User::findByPhone($phone);
//                            \Yii::error($phone);
                            if (isset($user)) {
                                $user->profile->max_id = "{$request->message->sender->user_id}";
                                if ($user->profile->save()) {
                                    $max->sendMessage(
                                        MessageBuilder::create(\Yii::t('telegram', 'message_menu'),)
                                            ->inlineKeyboard([
                                                MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t("telegram", "command_orders_my"), "command_orders_my")]),
                                                MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t("telegram", "command_orders_list"), "command_orders_list")]),
                                                MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t("telegram", "command_day_list"), "command_day_list")]),
                                                MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t("telegram", "command_start_report"), "command_start_report")]),
                                            ])
                                            ->format("html")
                                            ->build(),
                                        ["user_id" => $request->message->sender->user_id]
                                    );
                                } else {
                                    \Yii::error($user->profile->errors);
                                }
                                return true;
                            } else {
                                $max->sendMessage(
                                    MessageBuilder::create(\Yii::t("app", "command_contact_not_found!"))->build(),
                                    ["user_id" => $request->message->sender->user_id]
                                );
                            }
                        } catch(\Exception $e) {
                            \Yii::error($e);
                            return false;
                        }
                    }
                }
            }
        });
    }
}