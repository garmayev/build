<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Coworker $model */

$this->title = Yii::t('app', 'Coworkers profile');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Coworkers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$form = ActiveForm::begin([]);

echo $form->field($model, 'firstname');

echo $form->field($model, 'lastname');

echo $form->field($model, 'email')->textInput(['type' => 'email', 'disabled' => true]);

echo $form->field($model, 'phone');

echo $form->field($model, 'priority')->dropDownList(\app\models\Coworker::getPriorityList());

echo $form->field($model, 'category_id')->dropDownList(\yii\helpers\ArrayHelper::map(\app\models\Category::find()->all(), 'id', 'title'));

echo $form->field($model, 'created_by')->textInput(['disabled' => true, 'value' => $model->user->username]);

ActiveForm::end();