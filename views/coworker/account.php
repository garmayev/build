<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var app\models\forms\UserRegisterForm $registerForm */

$this->title = Yii::t('app', 'Coworkers account');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Coworkers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$registerForm = new \app\models\forms\UserRegisterForm();
$registerForm->findUser($model->id);

//var_dump( $registerForm );

$form = ActiveForm::begin();

echo $form->field($registerForm, 'username');

echo $form->field($registerForm, 'email');

echo $form->field($registerForm, 'current_password')->textInput(['type' => 'password', 'autocomplete' => 'new-password']);

echo $form->field($registerForm, 'new_password')->textInput(['type' => 'password', 'autocomplete' => 'new-password']);

echo Html::submitButton(\Yii::t('app', 'Save'), ['class' => 'btn btn-success']);

ActiveForm::end();