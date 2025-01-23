<?php

namespace app\modules\messenger\controllers;

class TelegramController extends \yii\web\Controller
{
    private $telegram;

    public function beforeAction($action)
    {
        $this->telegram = \Yii::$app->telegram;
        parent::beforeAction($action);
    }

    public function actionCallback()
    {
        
    }
}