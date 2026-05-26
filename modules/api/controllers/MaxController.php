<?php

namespace app\modules\api\controllers;

use app\models\SubscriptionForm;
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

    public function actionGetWebhook()
    {
        return $this->render('get-webhook');
    }

    public function actionAddWebhook() {
        $model = new SubscriptionForm();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                \Yii::$app->session->setFlash('success', \Yii::t('app', 'Webhook {url} added.', ['url' => $model->url]));
            } else {
                \Yii::$app->session->setFlash('error', \Yii::t('app', 'Unable to add webhook.'));
                \Yii::error($model->errors);
            }
        }
        return $this->render('add-webhook', [
            'model' => $model,
        ]);
    }

    public function actionDelete($url)
    {
        $model = new SubscriptionForm($url);
        if ($this->request->isPost) {
            if ($model->delete()) {
                \Yii::$app->session->setFlash('success', \Yii::t('app', 'Webhook {url} deleted.', ['url' => $url]));
            } else {
                \Yii::$app->session->setFlash('error', \Yii::t('app', 'Unable to delete webhook.'));
            }
        }
        return $this->redirect(['get-webhook']);
    }
}