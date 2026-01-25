<?php

namespace app\modules\api\controllers;

use yii\web\Controller;

class MaxController extends Controller
{
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionCallback()
    {
        /** @var garmayev\max\Max $max */
        $max = \Yii::$app->max;
        
        switch ($max->request->update_type) {
            case "bot_started":
                $max->sendMessage([
                    "text" => \Yii::t("app", "Welcome"), 
                    "attachments" => [
                        [
                            "type" => "inline_keyboard",
                            "payload" => [
                                "buttons" => [
                                    [
                                        ["type" => "request_contact", "text" => "Отправить контактный номер"]
                                    ]
                                ],
                            ]
                        ]
                    ],
                ], ["user_id" => $max->request->user->user_id]);
                break;
            case "message_created":
                foreach ($max->request->message->body->attachments as $attachment) {
                    if ($attachment->type === \garmayev\max\types\Attachment::TYPE_CONTACT) {
                        $vcard = \app\components\VCardParser::parseQuick($attachment->payload[0]->vcf_info);
                        $coworker = \app\models\Coworker::findByPhone($vcard['phones'][0]['number']);
                        $coworker->profile->max_id = "{$max->request->message->sender->user_id}";
                        if ($coworker->profile->save()) {
//                            \Yii::error($max->request->callback);
                            $max->sendMessage(["text" => \Yii::t("app", "Register Complete")], ["user_id" => $coworker->profile->max_id]);
                        } else {
                            \Yii::error($coworker->profile->errors);
                        }
                    }
                }
                break;
        }
    }

    public function actionWebhook($url)
    {
        \Yii::$app->max->setWebhook($url, ["message_created", "bot_started", "message_callback"]);
    }
}