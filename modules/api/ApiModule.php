<?php

namespace app\modules\api;

/**
 * api module definition class
 */
class ApiModule extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\api\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        \Yii::$app->user->enableSession = false;

//        $telegram = \Yii::$app->telegram;
        // custom initialization code goes here
    }
}
