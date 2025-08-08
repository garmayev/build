<?php

use app\models\Config;
use yii\web\View;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/**
 * @var $this View
 * @var $model Config
 */

$form = ActiveForm::begin([]);

echo $form->field($model, 'name')->textInput(['class' => 'disabled', 'disabled' => true]);

echo $form->field($model, 'value')->textInput(['type' => 'time']);

echo Html::submitButton(\Yii::t('app', 'Save'), ['class' => 'btn btn-success']);

ActiveForm::end();