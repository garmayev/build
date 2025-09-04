<?php

use app\components\Helper;
use app\models\Config;
use yii\web\View;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/**
 * @var $this View
 * @var $model Config
 */

$this->title = \Yii::t('app', 'Update');
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Intervals'), 'url' => ['config/interval']];
$this->params['breadcrumbs'][] = $this->title;

$form = ActiveForm::begin([]);

echo $form->field($model, 'name')->textInput(['class' => 'disabled', 'disabled' => true]);

echo $form->field($model, 'value')->textInput();

echo $form->field($model, 'label')->textInput();

echo Html::submitButton(\Yii::t('app', 'Save'), ['class' => 'btn btn-success']);

ActiveForm::end();