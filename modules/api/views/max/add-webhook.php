<?php

use \yii\helpers\Html;
use \yii\widgets\ActiveForm;

/**
 * @var $this \yii\web\View
 */

$form = ActiveForm::begin();
?>
<div class="mb-3">
    <label for="webhook" class="form-label"><?= \Yii::t('app', 'Webhook') ?></label>
    
<?php
echo Html::textInput('webhook', '', ['class' => 'form-control', 'id' => 'webhook']);
?>

</div>
<?php

echo Html::submitButton(\Yii::t('app', 'Save'), ['class' => 'btn btn-success']);

ActiveForm::end();