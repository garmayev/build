<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Property $model */

$this->title = Yii::t('app', 'Create Property');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Properties'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="property-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
