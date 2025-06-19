<?php

use app\models\forms\LoginForm;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var $this View
 * @var $model LoginForm
 * @var $form ActiveForm
 */

?>
<div class="row py-5">
    <div class="col-4 offset-4">
        <?php
            $form = ActiveForm::begin([
                'method' => 'post',
            ]);
            echo $form->field($model, 'username');
            echo $form->field($model, 'password')->passwordInput();
            echo $form->field($model, 'rememberMe')->checkbox();
            echo Html::submitButton(\Yii::t('app', 'Login'), ['class' => 'btn btn-primary col-4 offset-4']);
            ActiveForm::end();
        ?>
    </div>
</div>
<?php
