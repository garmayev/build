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
        $handler = $max->handler();
        $callbacks = [

        ];
        $handler->handle();
    }

    public function actionWebhook($url)
    {
        \Yii::$app->max->setWebhook($url, ["message_created", "bot_started", "message_callback"]);
    }
}