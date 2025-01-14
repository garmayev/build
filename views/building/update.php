<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Building $model */

$this->title = Yii::t('app', 'Update Building: {name}', [
    'name' => $model->title,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Buildings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="building-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
