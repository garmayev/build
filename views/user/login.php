<?php

use app\models\forms\LoginForm;
use yii\web\View;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/**
 * @var $this View
 * @var $model LoginForm
 * @var $form ActiveForm
 */

?>
<div class="row py-5">
    <div class="col-md-4 col-xs-19 offset-md-4 offset-xs-0">
        <?php
            $form = ActiveForm::begin([
                'method' => 'post',
            ]);
            echo $form->field($model, 'username');
            echo $form->field($model, 'password')->passwordInput();
            echo $form->field($model, 'rememberMe', [
    'template' => '<div class="form-check">{input}<span>{label}</span></div>'
])->checkbox([
    'labelOptions' => ['class' => 'form-check-label'], // Label class
    'inputOptions' => ['class' => 'form-check-input'] // Input class
]);
            echo Html::submitButton(\Yii::t('app', 'Login'), ['class' => 'btn btn-primary col-4 offset-4']);
            ActiveForm::end();
        ?>
    </div>
</div>
<?php
