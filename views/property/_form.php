<?php

use app\models\Dimension;
use app\models\Property;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @var View $this
 * @var Property $model
 * @var ActiveForm $form
 */
?>

<div class="property-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'dimensions')->widget(Select2::class, [
        'data' => ArrayHelper::map(Dimension::find()->all(), 'id', 'title'),
        'options' => [
            'multiple' => true,
        ],
        'pluginOptions' => [
            'tags' => false,
        ]
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
