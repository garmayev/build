<?php

use app\models\User;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @var View $this
 * @var User $model
 * @var ActiveForm $form
 */

$this->registerJsVar('token', \Yii::$app->user->identity->access_token);

echo Html::tag('div', '', [
    'id' => 'coworker-form',
    'class' => 'container-fluid my-3',
    'data-index' => $model->isNewRecord ? "" : $model->id,
    'data-lang' => 'ru',
    'data-is-new' => $model->isNewRecord ? "true" : "false",
]);