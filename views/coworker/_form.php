<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Category;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\Coworker $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="coworker-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'user[username]')->textInput()->label(\Yii::t('app', 'Username')) ?>

    <?= $form->field($model, 'user[email]')->textInput()->label(\Yii::t('app', 'Email')) ?>

    <?= $form->field($model, 'category_id')->dropDownList(ArrayHelper::map(Category::find()->all(), 'id', 'title')) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
