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

<<<<<<< HEAD
//        $telegram = \Yii::$app->telegram;
=======
        $telegram = \Yii::$app->telegram;
>>>>>>> a0d55bd (Server Fix)
        // custom initialization code goes here
    }
}
