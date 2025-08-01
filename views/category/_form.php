<?php

use app\models\Category;
use app\models\Order;
use app\models\Property;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Category $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="category-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->dropDownList([
        Order::TYPE_COWORKER => \Yii::t('app', 'Coworker'),
        Order::TYPE_MATERIAL => \Yii::t('app', 'Material'),
        Order::TYPE_TECHNIQUE => \Yii::t('app', 'Technique')
    ]) ?>

    <?php
    echo $form->field($model, 'properties[]')->widget(Select2::class, [
        'data' => ArrayHelper::map(Property::find()->all(), 'id', 'title'),
        'options' => [
                'multiple' => true,
        ]
    ]);
//    echo $form->field($model, 'properties')->dropDownList(ArrayHelper::map(Property::find()->all(), 'id', 'title'), ['multiple' => true]);
    ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
