<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Property $model */

$this->title = Yii::t('app', 'Update Property: {name}', [
    'name' => $model->title,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Properties'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="property-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
