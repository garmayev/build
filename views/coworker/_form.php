<?php

use app\models\Coworker;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @var View $this
 * @var Coworker $model
 * @var ActiveForm $form
 */

$this->registerJsVar('token', \Yii::$app->user->identity->access_token);

echo $this->render('account', [
    'model' => $model,
]);