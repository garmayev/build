<?php

use \yii\helpers\Html;
use \yii\widgets\ActiveForm;

/**
 * @var $this \yii\web\View
 * @var $model \app\models\forms\SubscriptionForm
 */

$form = ActiveForm::begin();

echo $form->field($model, 'url');

echo $form->field($model, 'update_types')->checkboxList($model->getTypeLabels(), [
    'item' => function ($index, $label, $name, $checked, $value) {
        $checkedAttr = $checked ? 'checked' : '';
        return '<div class="col-6">
                    <label>
                        <input type="checkbox" name="' . $name . '" value="' . $value . '" ' . $checkedAttr . '>
                        ' . $label . '
                    </label>
                </div>';
    },
    'class' => 'row',
]);

echo Html::submitButton(\Yii::t('app', 'Save'), ['class' => 'btn btn-success']);

ActiveForm::end();